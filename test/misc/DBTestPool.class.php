<?php
	namespace Onphp\Test;

	final class DBTestPool extends \Onphp\Singleton implements \Onphp\Instantiatable
	{
		private $pool = array();
		
		/**
		 * @return \Onphp\Test\DBTestPool
		 */
		public static function me()
		{
			return \Onphp\Singleton::getInstance(__CLASS__);
		}
		
		protected function __construct(array $dbs = array())
		{
			\Onphp\Assert::isArray($dbs);
			
			foreach ($dbs as $connector => $credentials) {
				$this->pool[$connector] = DB::spawn(
					$connector,
					$credentials['user'],
					$credentials['pass'],
					$credentials['host'],
					$credentials['base']
				);
			}
		}
		
		public function disconnect()
		{
			foreach ($this->pool as $connector)
				$connector->disconnect();
		}
		
		public function connect($persistent = false)
		{
			foreach ($this->pool as $connector) {
				$connector->setPersistent($persistent)->connect();
			}
		}
		
		public function getPool()
		{
			return $this->pool;
		}
	}
?>