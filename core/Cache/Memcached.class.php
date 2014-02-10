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

	/**
	 * Memcached-based cache.
	 *
	 * @see http://www.danga.com/memcached/
	 *
	 * @ingroup Cache
	**/
	class Memcached extends CachePeer
	{
		const DEFAULT_PORT		= 11211;
		const DEFAULT_HOST		= '127.0.0.1';
		const DEFAULT_BUFFER	= 16384;
		
		private $link		= null;

		private $timeout	= null;
		
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
		
		public function __destruct()
		{
			try {
				fclose($this->link);
			} catch (BaseException $e) {/*_*/}
		}

		/**
		 * @return Memcached
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
		 * @return Memcached
		**/
		public function clean()
		{
			if (!$this->link) {
				$this->alive = false;
				return null;
			}
			
			$this->sendRequest("flush_all\r\n");
			
			// flushing obligatory response - "OK\r\n"
			fread($this->link, 4);
			
			return parent::clean();
		}
		
		public function getList($indexes)
		{
			if (!$this->link) {
				$this->alive = false;
				return null;
			}
			
			$command = 'get '.implode(' ', $indexes)."\r\n";
			
			if (!$this->sendRequest($command))
				return null;
			
			// we can't deserialize objects inside parseGetRequest,
			// because of possibility further requests to memcached
			// during deserialization - in __wakeup(), for example
			return unserialize($this->parseGetRequest(false));
		}
		
		public function increment($key, $value)
		{
			return $this->changeInteger('incr', $key, $value);
		}
		
		public function decrement($key, $value)
		{
			return $this->changeInteger('decr', $key, $value);
		}
		
		public function get($index)
		{
			if (!$this->link) {
				$this->alive = false;
				return null;
			}

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

			if ($this->isTimeout())
				return false;

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

			if ($this->isTimeout())
				return false;

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
			
			if (!is_numeric($value)) {
				if (is_string($value))
					$packed = $value;
				else {
					$packed = serialize($value);
					
					$flags |= 1;
				}
				
				if ($this->compress) {
					$compressed = gzcompress($packed);
					
					if (strlen($compressed) < strlen($packed)) {
						$packed = $compressed;
						$flags |= 2;
						unset($compressed);
					}
				}
			} elseif (
				Assert::checkFloat($value)
				&& ((int) $value != (float) $value)
			) {
				$packed = serialize($value);
				
				$flags |= 1;
			} else
				$packed = $value;
			
			$lenght = strlen($packed);
			
			$command = "{$method} {$index} {$flags} {$expires} {$lenght}\r\n{$packed}\r\n";
			
			if (!$this->sendRequest($command))
				return false;
			
			$response = fread($this->link, $this->buffer);

			if ($this->isTimeout())
				return false;
			
			if ($response === "STORED\r\n")
				return true;
			
			return false;
		}
		
		private function parseGetRequest($single)
		{
			$result = null;
			$index = 0;
			
			while ($header = fgets($this->link, 8192)) {
				if (
					($header === "END\r\n")
					|| ($header === "ERROR\r\n")
				)
					break;
				
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
					
					if ($single) {
						fread($this->link, 7); // skip "\r\nEND\r\n"
						
						if ($flags & 1)
							$value = unserialize($value);
						else
							// help in case when 100 was decreased to 99
							// memcached will not honor output lenght then
							$value = rtrim($value);
						
						return $value;
					} else {
						fread($this->link, 2); // skip "\r\n"
						
						$index++;
						
						if (is_numeric($key)) {
							$result .= 'i:'.$key.';';
						} else {
							$result .= 's:'.strlen($key).':"'.$key.'";';
						}
						
						if ($flags & 1)
							$result .= $value;
						elseif (is_numeric($value))
							$result .= 'i:'.$value.';';
						else // string
							$result .= 's:'.$bytes.':"'.$value.'";';
					}
				} else
					break;
			}

			if ($this->isTimeout())
				return null;

			if ($single)
				return $result;
			else
				return 'a:'.$index.':{'.$result.'}';
		}
		
		private function changeInteger($command, $key, $value)
		{
			if (!$this->link)
				return null;
			
			$command = "{$command} {$key} {$value}\r\n";
			
			if (!$this->sendRequest($command))
				return null;
			
			try {
				$response = rtrim(fread($this->link, $this->buffer));
			} catch (BaseException $e) {
				return null;
			}

			if ($this->isTimeout())
				return null;

			if (is_numeric($response))
				return (int) $response;
			
			return null;
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
							substr($command, $offset, $this->buffer)
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
						fwrite($this->link, $command, $commandLenght) !== false;
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

        public function keys($pattern = null) {
            if (!$this->link) {
                $this->alive = false;
                return null;
            }

            if (is_string($pattern) && strlen($pattern)) {
                $command = "keys {$pattern}\r\n";
            } else {
                $command = "keys\r\n";
            }

            if (!$this->sendRequest($command))
                return null;

            $result = array();

            while ($header = trim(fgets($this->link, $this->buffer))) {
                $result[] = $header;
            }

            if ($this->isTimeout())
                return null;

            return $result;
        }

        public function deleteList(array $keys) {

            if (!$this->link) {
                $this->alive = false;
                return false;
            }

            if (0 == count($keys))
                return false;

            $keys = implode(' ', $keys);
            if (!$this->sendRequest("deletes {$keys}\r\n"))
                return false;

            try {
                $response = fread($this->link, $this->buffer);
            } catch (BaseException $e) {
                return false;
            }

            if ($this->isTimeout())
                return false;

            return $response;
        }

        public function deleteByPattern($pattern) {

            if (!$this->link) {
                $this->alive = false;
                return null;
            }

            if (is_string($pattern) && strlen($pattern)) {

                if (!$this->sendRequest("delete_by_pattern {$pattern}\r\n"))
                    return false;

                try {
                    $response = fread($this->link, $this->buffer);
                } catch (BaseException $e) {
                    return false;
                }

                if ($this->isTimeout())
                    return false;

                return $response;
            } else {
                return null;
            }
        }
    }
?>
