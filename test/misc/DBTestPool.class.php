<?php
	/* $Id$ */
	
	final class DBTestPool extends Singleton
	{
		private $pool	= array();
		private $info	= array();
		
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function __construct(/* array */ $dbs)
		{
			Assert::isArray($dbs);
			
			foreach ($dbs as $connector => $credentials) {
				$this->pool[$connector] = new $connector();
				$this->info[$connector] = $credentials;
			}
		}
		
		public function disconnect()
		{
			foreach ($this->pool as $connector)
				$connector->disconnect();
		}
		
		public function connect($persistent = false)
		{
			foreach ($this->info as $connector => $credentials) {
				$this->pool[$connector]->connect(
					$credentials['user'], $credentials['pass'],
					$credentials['host'], $credentials['base'],
					$persistent === true
				);
			}
		}
		
		public function getPool()
		{
			return $this->pool;
		}
	}
?>