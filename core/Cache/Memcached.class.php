<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 *   Inspired by the work of Ryan Gilfether <hotrodder@rocketmail.com>     *
 *   Copyright (c) 2003, under the GNU GPL license                         *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Memcached-based cache.
	 * 
	 * @see http://www.danga.com/memcached/
	 * 
	 * @ingroup Cache
	**/
	final class Memcached extends CachePeer
	{
		const DEFAULT_PORT		= 11211;
		const DEFAULT_HOST		= '127.0.0.1';
		const DEFAULT_BUFFER	= 16384;
		
		private $link		= null;
		
		private $buffer		= Memcached::DEFAULT_BUFFER;
		
		/**
		 * @return Memcached
		**/
		public static function create(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT,
			$buffer = Memcached::DEFAULT_BUFFER
		)
		{
			return new Memcached($host, $port, $buffer);
		}
		
		public function __construct(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT,
			$buffer = Memcached::DEFAULT_BUFFER
		)
		{
			$errno = $errstr = null;
			
			try {
				if ($this->link = @fsockopen($host, $port, $errno, $errstr, 1)) {
					$this->alive = true;
				
					$this->buffer = $buffer;
				
					stream_set_blocking($this->link, true);
				}
			} catch (BaseException $e) {/*_*/}
		}
		
		/**
		 * @return Memcached
		**/
		public function clean()
		{
			$this->sendRequest("flush_all\r\n");
			
			// flushing obligatory response - "OK\r\n"
			fread($this->link, 4);
			
			return parent::clean();
		}
		
		public function getList($indexes)
		{
			if (!$this->link)
				return null;
			
			$command = 'get '.implode(' ', $indexes)."\r\n";
			
			if (!$this->sendRequest($command))
				return null;
			
			return $this->parseGetRequest(false);
		}
		
		public function get($index)
		{
			if (!$this->link)
				return null;
			
			$command = "get {$index}\r\n";
			
			if (!$this->sendRequest($command))
				return null;
			
			return $this->parseGetRequest(true);
		}
		
		public function delete($index, $time = null)
		{
			$command =
				$time
					? "delete {$index} {$time}\r\n"
					: "delete {$index}\r\n";
			
			if (!$this->sendRequest($command))
				return false;
			
			try {
				$response = fread($this->link, $this->buffer);
			} catch (BaseException $e) {
				return false;
			}
			
			if ($response === "DELETED\r\n")
				return true;
			else
				return false;
		}
		
		public function append($key, $data)
		{
			$packed = serialize($data);
			
			$length = strlen($packed);
			
			// flags and exptime are ignored
			$command = "append {$key} 0 0 {$length}\r\n{$packed}\r\n";
			
			if (!$this->sendRequest($command))
				return false;
			
			$response = fread($this->link, $this->buffer);
			
			if ($response === "STORED\r\n")
				return true;
			
			return false;
		}
		
		protected function store(
			$method, $index, $value, $expires = Cache::EXPIRES_MINIMUM
		)
		{
			if ($expires === Cache::DO_NOT_CACHE)
				return false;
			
			$flags = 0;
			
			if (!is_scalar($value) || $value === Cache::NOT_FOUND) {
				$packed = serialize($value);
				$flags |= 1;
				
				if ($this->compress) {
					$compressed = gzcompress($packed);
					
					if (strlen($compressed) < strlen($packed)) {
						$packed = $compressed;
						$flags |= 2;
						unset($compressed);
					}
				}
			} else
				$packed = $value;
			
			$lenght = strlen($packed);
			
			$command = "{$method} {$index} {$flags} {$expires} {$lenght}\r\n{$packed}\r\n";
			
			if (!$this->sendRequest($command))
				return false;
			
			$response = fread($this->link, $this->buffer);
			
			if ($response === "STORED\r\n")
				return true;
			
			return false;
		}
		
		private function parseGetRequest($single)
		{
			$result = null;
			
			while ($header = fgets($this->link, 8192)) {
				if ($header === "END\r\n")
					return $result;
				elseif ($header === "ERROR\r\n")
					return $result;
				
				$array = explode(' ', rtrim($header, "\r\n"), 4);
				
				if (count($array) <> 4)
					continue;
				else
					list(, $key, $flags, $bytes) = $array;
				
				if (
					is_string($key)
					&& is_numeric($flags)
					&& is_numeric($bytes)
				) {
					$value = stream_get_contents($this->link, $bytes);
					
					if ($flags & 2)
						$value = gzuncompress($value);
					
					if ($flags & 1)
						$value = unserialize($value);
					
					if ($single) {
						fread($this->link, 7); // skip "\r\nEND\r\n"
						
						return $value;
					} else {
						fread($this->link, 2); // skip "\r\n"
						
						$result[] = $value;
					}
				} else
					return $result;
			}
			
			return $result;
		}
		
		private function sendRequest($command)
		{
			$commandLenght = strlen($command);
			
			if ($commandLenght > $this->buffer) {
				$offset = 0;
				while ($offset < $commandLenght) {
					try {
						$result = fwrite(
							$this->link,
							substr(
								$command,
								$offset,
								$this->buffer
							)
						);
					} catch (BaseException $e) {
						return $this->alive = false;
					}
					
					if ($result !== false)
						$offset += $result;
					else
						return false;
				}
			} else {
				try {
					return (
						fwrite(
							$this->link,
							$command,
							$commandLenght
						) === false
							? false
							: true
					);
				} catch (BaseException $e) {
					return $this->alive = false;
				}
			}
			
			return true;
		}
	}
?>