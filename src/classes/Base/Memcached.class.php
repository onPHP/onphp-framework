<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   Based on version by Ryan Gilfether <hotrodder@rocketmail.com>         *
 *   Copyright (c) 2003                                                    *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class Memcached
	{
		const EXPIRES_FOREVER	= 259200; // 3 days
		const EXPIRES_MAXIMUM	= 21600; // 6 hrs
		const EXPIRES_MEDIUM	= 3600; // 1 hr
		const EXPIRES_MINIMUM	= 300; // 5 mins
		
		const DO_NOT_CACHE		= -2005;
		
		const DEFAULT_PORT		= 11211;

		private static $instance = null;
		
		private $servers		= array();
		private $sockets		= array();
		
		private function __construct() {/* it's a singletone */}
		
		public static function getInstance()
		{
			if (Memcached::$instance)
				return Memcached::$instance;

			Memcached::$instance = new Memcached();
			
			return Memcached::$instance;
		}
		
		public function addServer($host, $port = self::DEFAULT_PORT)
		{
			if ($socket = $this->connectTo($host, $port)) {
				$count = sizeof($this->servers);
				$this->servers[$count][0] = $host;
				$this->servers[$count][1] = $port;
				$this->sockets[$count] = $socket;
			}
			
			return $this;
		}
		
		public function disconnect()
		{
			for ($i = 0; $i < sizeof($this->servers); $i++)
				fclose($this->sockets[$i]);

			$this->sockets = array();

			return true;
		}
		
		public function reconnect()
		{
			$this->disconnect();
			
			for ($i = 0; $i < sizeof($this->servers); $i++)
				$this->connect($i);

			return true;
		}
		
		public function dropEverything()
		{
			for ($i = 0; $i < sizeof($this->servers); $i++) {
				$this->sendRequest($this->sockets[$i], "flush_all\r\n");
				fread($this->sockets[$i], MEMCACHED_BUFFER);
			}

			return true;
		}
		
		public function get($index)
		{
			$socket = &$this->getSocket($this->getServerId($index));
			
			if (!$socket)
				return null;

			$command = "get {$index}\r\n";
			
			if (!$this->sendRequest($socket, $command))
				return null;

			$buffer = '';
			$lenght = 0;
			$bytesRead = 0;
			
			while ($line = fread($socket, MEMCACHED_BUFFER)) {
				if ($line === false)
					return null;

				if ($lenght === 0) {
					$header = substr($line, 0, strpos($line, "\r\n"));
					
					if ($header === 'ERROR')
						return null;

					if ($header !== 'END') {
						$array = explode(' ', $header, 4);

						if (sizeof($array) <> 4)
							continue;
						else
							list ($crap, $key, $flags, $bytes) = explode(' ', $header);
						
						if (is_string($key) && is_numeric($flags) && is_numeric($bytes))
							$line = substr($line, strpos($line, "\r\n") + 2, strlen($line));
						else
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
			
			return false;
		}
		
		public function set($index, &$value, $expires = 0)
		{
			return $this->store('set', $index, $value, $expires);
		}
		
		public function add($index, &$value, $expires = 0)
		{
			return $this->store('add', $index, $value, $expires);
		}
		
		public function replace($index, &$value, $expires = 0)
		{
			return $this->store('replace', $index, $value, $expires);
		}
		
		public function isFunctional()
		{
			return (sizeof($this->sockets) ? true : false);
		}
		
		private function store($method, $index, &$value, $expires)
		{
			if ($expires === self::DO_NOT_CACHE)
				return false;
			elseif (!$expires)
				$expires = self::EXPIRES_MINIMUM;

			$socket = &$this->getSocket($this->getServerId($index));

			if (!$socket)
				return false;

			$flags = 0;
			
			if (!is_scalar($value)) {
				$packed = serialize($value);
				$flags |= 1;

				if (MEMCACHED_COMPRESSION) {
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
			
			$result = $this->sendRequest($socket, $command);
			$response = trim(fread($socket, MEMCACHED_BUFFER));
			
			if ($response === 'STORED')
				return true;
			elseif ($response === 'NOT_STORED')
				return false;
			elseif ($response === 'ERROR')
				return false;
		}

		public function delete($index)
		{
			$socket = &$this->getSocket($this->getServerId($index));
			$command = "delete $index\r\n";
			$result = $this->sendRequest($socket, $command);

			try {
				$response = trim(fread($socket, MEMCACHED_BUFFER));
			} catch (BaseException $e) {
				return false;
			}

			if ($response === 'DELETED')
				return true;
			else
				return false;
		}
		
		private function sendRequest(&$socket, $command)
		{
			$commandLenght = strlen($command);
			
			if ($commandLenght > MEMCACHED_BUFFER) {
				$offset = 0;
				while ($offset < $commandLenght) {
					try {
						$result = fwrite($socket, substr($command, $offset, MEMCACHED_BUFFER));
					} catch (BaseException $e) {
						return false;
					}
					
					if ($result !== false)
						$offset += $result;
					else
						return false;
				}
			} else {
				try {
					return (fwrite($socket, $command, $commandLenght) === false ? false : true);
				} catch (BaseException $e) {
					return false;
				}
			}
		}
		
		private function dropServer($id)
		{
			unset(
				$this->servers[$id],
				$this->sockets[$id]
			);

			$this->servers	= array_values($this->servers);
			$this->sockets	= array_values($this->sockets);

			return $this;
		}

		private function getSocket($id)
		{
			if (!isset($this->sockets[$id]) || !is_resource($this->sockets[$id]))
				return $this->connect($id);

			return $this->sockets[$id];
		}
		
		private function getServerId($index)
		{
			/*
				algorithm suggested by Dimitry Mardiyan and Donald E. Knuth
				under the GNU General Public License, of course
			*/

			$hash = sprintf("%u", md5($index));
			return (int) (($hash % 23) * sizeof($this->sockets) / 23);
		}
		
		private function connectTo($host, $port)
		{
			try {
				$socket = fsockopen($host, $port);
			} catch (BaseException $e) {
				return null;
			}
			
			stream_set_blocking($socket, true);
			stream_set_timeout($socket, 1);
			
			return $socket;
		}
		
		private function connect($serverId)
		{
			$socket =
				$this->connectTo(
					$this->servers[$serverId][0],
					$this->servers[$serverId][1]
				);

			if ($socket)
				return $this->sockets[$serverId] = $socket;
			else {
				$this->dropServer($serverId);
				return false;
			}
		}
	}
?>