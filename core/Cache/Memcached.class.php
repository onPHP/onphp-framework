<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
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
		
		public function clean()
		{
			$this->sendRequest("flush_all\r\n");
			
			// flushing obligatory response - "OK\r\n"
			fread($this->link, 4);
			
			return $this;
		}
		
		public function get($index)
		{
			if (!$this->link)
				return null;
			
			$command = "get {$index}\r\n";
			
			if (!$this->sendRequest($command))
				return null;
			
			$buffer = null;
			$lenght = 0;
			$bytesRead = 0;
			
			while ($line = fread($this->link, $this->buffer)) {
				if ($line === false)
					return null;
				
				if ($lenght === 0) {
					$header = substr($line, 0, strpos($line, "\r\n"));
					
					if ($header === 'ERROR')
						return null;
					
					if ($header !== 'END') {
						$array = explode(' ', $header, 4);
						
						if (count($array) <> 4)
							continue;
						else
							list(
								$crap, $key, $flags, $bytes
							) = explode(' ', $header);
						
						if (
							is_string($key)
							&& is_numeric($flags)
							&& is_numeric($bytes)
						) {
							$line =
								substr(
									$line,
									strpos($line, "\r\n") + 2,
									strlen($line)
								);
						} else
							return null;
						
						$lenght = $bytes;
					} else
						return null;
				}
				
				$bytesRead += strlen($line);
				
				$buffer .= $line;
				
				// strlen("\r\nEND\r\n") == 7
				if ($bytesRead == ($lenght + 7)) {
					$end = substr($buffer, $lenght + 2, 3);
					
					if ($end === 'END') {
						$result = substr($buffer, 0, $lenght);
						
						if ($flags & 2)
							$result = gzuncompress($result);
						
						if ($flags & 1)
							$result = unserialize($result);
						
						return $result;
					} else
						return null;
				}
			}
			
			return null;
		}
		
		public function delete($index, $time = null)
		{
			$command =
				$time
					? "delete {$index} {$time}\r\n"
					: "delete {$index}\r\n";
			
			$result = $this->sendRequest($command);
			
			try {
				$response = trim(fread($this->link, $this->buffer));
			} catch (BaseException $e) {
				return false;
			}
			
			if ($response === 'DELETED')
				return true;
			else
				return false;
		}
		
		protected function store(
			$method, $index, &$value, $expires = Cache::EXPIRES_MINIMUM
		)
		{
			if ($expires === Cache::DO_NOT_CACHE)
				return false;
			
			$flags = 0;
			
			if (!is_numeric($value) || $value === Cache::NOT_FOUND) {
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
			
			$result = $this->sendRequest($command);
			$response = trim(fread($this->link, $this->buffer));
			
			if ($response === 'STORED')
				return true;
			elseif ($response === 'NOT_STORED')
				return false;
			elseif ($response === 'ERROR')
				return false;
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
		}
	}
?>