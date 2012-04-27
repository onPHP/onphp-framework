<?php
	/**
	 * @author Artem Naumenko
	 * @copyright (C) 2012-2012 by Artem Naumenko
	 */
	final class RedisNoSQL extends CachePeer {
		const DEFAULT_HOST = "localhost";
		const DEFAULT_PORT = "6379";
		const DEFAULT_TIMEOUT = 1.0;
		
		private $redis		= null;
		private $host		= null;
		private $port		= null;
		private $timeout	= null;
		
		public static function create(
			$host = self::DEFAULT_HOST,
			$port = self::DEFAULT_PORT,
			$timeout = self::DEFAULT_TIMEOUT
		)
		{
			return new self($host, $port, $timeout);
		}

		public function __construct(
			$host = self::DEFAULT_HOST,
			$port = self::DEFAULT_PORT,
			$timeout = self::DEFAULT_TIMEOUT
		)
		{
			if (!extension_loaded('redis')) {
				throw new Exception('Install phpredis https://github.com/nicolasff/phpredis/');
			}
			
			$this->host		= $host;
			$this->port		= $port;
			$this->timeout	= $timeout;
			
			$this->redis = new redis();
			
			$this->alive = $this->redis->pconnect($this->host, $this->port, $this->timeout);
			
		}

		protected function store($action, $key, $value, $expires = Cache::EXPIRES_MEDIUM){
			switch ($action) {
				case "set":
				case "replace":
					$this->redis->setEx($key, $expires, $value);
					break;
				case "add":
					$this->redis->append($key, $value);
					break;
				default:
					throw new NotImplementedException();
			}
		}

		public function append($key, $data) {
			$this->redis->append($key, $data);
			
			return $this;
		}

		public function decrement($key, $value) {
			$this->redis->decrBy($key, $value);
			
			return $this;
		}

		public function delete($key) {
			$this->redis->delete($key);
			
			return $this;
		}

		public function get($key) {
			return $this->redis->get($key);
		}

		public function increment($key, $value) {
			$this->redis->incrBy($key, $value);
			
			return $this;
		}

		/**
		 * @param string $key 
		 * 
		 * @return IList
		 */
		public function getList($key)
		{
			return new RedisNoSQLList($this->redis, $key);
		}
		
		/**
		 * @param string $key 
		 * 
		 * @return ISet
		 */
		public function getSet($key)
		{
			throw new NotImplementedException();
		}

	}

