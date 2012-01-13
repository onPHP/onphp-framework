<?php
/***************************************************************************
 *   Copyright (C) 2012 by Igor V. Gulyaev                                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Redis-based cache.
	 *
	 * @see http://redis.io/topics/protocol
	 *
	 * @ingroup Cache
	**/
	final class Redis extends CachePeer
	{
		const DEFAULT_PORT		= 6379;
		const DEFAULT_HOST		= '127.0.0.1';
		const DEFAULT_BUFFER	= 16384;
		
		private $link		= null;
		private $timeout	= null;
		private $buffer		= self::DEFAULT_BUFFER;

		/**
		 * @return Redis
		**/
		public static function create(
			$host = self::DEFAULT_HOST,
			$port = self::DEFAULT_PORT,
			$buffer = self::DEFAULT_BUFFER
		)
		{
			return new self($host, $port, $buffer);
		}
		
		public function __construct(
			$host = self::DEFAULT_HOST,
			$port = self::DEFAULT_PORT,
			$buffer = self::DEFAULT_BUFFER
		)
		{
			$errno = $errstr = null;
			
			try {
				if ($this->link = fsockopen($host, $port, $errno, $errstr, 1)) {
					$this->alive = true;
					
					$this->buffer = $buffer;
					
					stream_set_blocking($this->link, true);
				}
			} catch (BaseException $e) {/*_*/}
		}
		
		public function __destruct()
		{
			try {
				fclose($this->link);
			} catch (BaseException $e) {/*_*/}
		}

		/**
		 * @return Redis
		 */
		public function setTimeout($microseconds)
		{
			Assert::isGreater($microseconds, 0);
			
			$this->timeout = $microseconds;

			if ($this->alive) {
				$seconds = floor($microseconds / 1000);
				$fraction = $microseconds - ($seconds * 1000);
				
				stream_set_timeout($this->link, $seconds, $fraction);
			}
			
			return $this;
		}

		/**
		 * @return Redis
		**/
		public function clean()
		{
			if (!$this->link) {
				$this->alive = false;
				return null;
			}
			
			$this->sendRequest(array('flushall'));
			$this->getResponse();

			return parent::clean();
		}
		
		public function getList($indexes)
		{
			if (!$this->link) {
				$this->alive = false;
				return null;
			}

			$command = array_merge(array('mget'), $indexes);

			if (!$this->sendRequest($command))
				return null;
			
			$response = $this->getResponse();
			if (is_array($response)) {
				$response = array_combine($indexes, $response);
				$response = array_filter($response, array('Redis', 'emptyFilter'));
			}

			return $response;
		}

		public static function emptyFilter($var)
		{
			return ($var !== null);
		}

		public function increment($key, $value)
		{
			return $this->changeInteger('incrby', $key, $value);
		}
		
		public function decrement($key, $value)
		{
			return $this->changeInteger('decrby', $key, $value);
		}

		public function get($index)
		{
			if (!$this->link) {
				$this->alive = false;
				return null;
			}

			if (!$this->sendRequest(array('get', $index)))
				return null;

			return $this->getResponse();
		}
		
		public function delete($index, $time = null)
		{
			if (!$this->sendRequest(array('del', $index)))
				return false;

			try {
				$response = $this->getResponse();
			} catch (BaseException $e) {
				return false;
			}

			if ($this->isTimeout())
				return false;

			return ($response == '1');
		}
		
		public function append($key, $data)
		{
			$value = $this->get($key);
			if (!$value)
				$value = '';

			if (!$this->sendRequest(array('append', $key, $value)))
				return false;

			$response = $this->getResponse();

			if ($this->isTimeout())
				return false;

			if ($response == "OK")
				return true;

			return false;
		}

		protected function store(
			$method, $index, $value, $expires = Cache::EXPIRES_MINIMUM
		)
		{
			if ($expires === Cache::DO_NOT_CACHE)
				return false;

// incrby decrby append not work properly with expire
//			$methodMap = array(
//				'add' => 'setex',
//				'replace' => 'setex',
//				'set' =>' setex'
//			);

			$methodMap = array(
				'add' => 'set',
				'replace' => 'set',
				'set' => 'set'
			);

			$method = $methodMap[$method];

// incrby decrby append not work properly with expire
//			if (!$this->sendRequest(array($method, $index, $expires, $value)))
			if (!$this->sendRequest(array($method, $index, $value)))
				return false;
			
			$response = $this->getResponse();

			if ($this->isTimeout())
				return false;
			
			if ($response == "OK")
				return true;
			
			return false;
		}
		
		private function changeInteger($command, $key, $value)
		{
			if (!$this->link)
				return null;
			
			if (!$this->sendRequest(array($command, $key, $value)))
				return null;
			
			try {
				$response = $this->getResponse();
			} catch (BaseException $e) {
				return null;
			}

			if ($this->isTimeout())
				return null;

			if (is_numeric($response))
				return (int) $response;
			
			return null;
		}

		private function getResponse()
		{
			$parserMap = array(
				'+' => 'singleLine',
				'-' => 'error',
				':' => 'integer',
				'$' => 'bulk',
				'*' => 'multiBulk',
			);

			$responseType = null;
			$buffer = fgets($this->link, 8192);
			if ($buffer) {
				$responseType = substr($buffer, 0, 1);
				$response = $buffer;
			}

			if ($this->isTimeout())
				return null;

			if (key_exists($responseType, $parserMap)) {
				$parseMethod = 'parse'.ucfirst($parserMap[$responseType]);

				$response = rtrim($response, "\r\n ");
				$response = mb_substr($response, 1, mb_strlen($response)-1);
				return $this->$parseMethod(trim($response, "\r\n "));
			} else {
//				throw new WrongArgumentException('unknown response type in '.$response);
				return $response;
			}
		}

		private function parseMultiBulk($response)
		{
			$result = array();
			for ($i=0; $i < $response; $i++) {
				$buffer = fgets($this->link, 8192);
				$buffer = rtrim($buffer, "\r\n ");
				$buffer = mb_substr($buffer, 1, mb_strlen($buffer)-1);
				$result[] = $this->parseBulk($buffer);
			}
			return $result;
		}

		private function parseBulk($response)
		{
			if ($response == -1) {
				return null;
			}
			$buffer = fgets($this->link, 8192);
			$result = rtrim($buffer, "\r\n ");

			if (strlen($result) != $response) {
				return null;
			}

			return $result;
		}

		private function parseError($response)
		{
			return null;
		}

		private function parseInteger($response)
		{
			return $response;
		}

		private function parseSingleLine($response)
		{
			return $response;
		}

		private function sendRequest(array $command)
		{
			$commandString = '*'.count($command)."\r\n";
			foreach ($command as $item) {
				$commandString .= '$'.strlen($item)."\r\n".$item."\r\n";
			}

			$commandLenght = strlen($commandString);

			if ($commandLenght > $this->buffer) {
				$offset = 0;
				while ($offset < $commandLenght) {
					try {
						$result = fwrite(
							$this->link,
							substr($commandString, $offset, $this->buffer)
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
					return
						fwrite($this->link, $commandString, $commandLenght) !== false;
				} catch (BaseException $e) {
					return $this->alive = false;
				}
			}

			if ($this->isTimeout())
				return false;
			
			return true;
		}

		private function isTimeout()
		{
			if (!$this->timeout)
				return false;

			$meta = stream_get_meta_data($this->link);

			return $meta['timed_out'];
		}
	}
?>
