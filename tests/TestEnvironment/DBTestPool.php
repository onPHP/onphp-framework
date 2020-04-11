<?php

namespace OnPHP\Tests\TestEnvironment;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\Instantiatable;
use OnPHP\Core\Base\Singleton;
use OnPHP\Core\DB\DB;
use OnPHP\Core\DB\MySQLim;

final class DBTestPool extends Singleton implements Instantiatable
{
	private $pool = array();

	/**
	 * @return DBTestPool
	 */
	public static function me()
	{
		return Singleton::getInstance(__CLASS__);
	}

	protected function __construct(array $dbs = array())
	{
		Assert::isArray($dbs);

		foreach ($dbs as $connector => $credentials) {
			$this->pool[$connector] = DB::spawn(
				$connector,
				$credentials['user'],
				$credentials['pass'],
				$credentials['host'],
				$credentials['base']
			);
			if ($this->pool[$connector] instanceof MySQLim) {
				$this->pool[$connector]->setDefaultEngine('INNODB');
			}
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