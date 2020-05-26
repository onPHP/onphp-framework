<?php

namespace OnPHP\Tests\DB;

use OnPHP\Core\DB;
use OnPHP\Core\DB\DBPool;
use OnPHP\Core\DB\MySQLim;
use OnPHP\Core\DB\PgSQL;
use OnPHP\Core\DB\SQLitePDO;
use OnPHP\Core\Exception\DatabaseException;
use OnPHP\Tests\TestEnvironment\DBTestPool;
use OnPHP\Tests\TestEnvironment\TestCaseDAO;
use OnPHP\Tests\Meta\Business\TestCity;

/**
 * @group core
 * @group db
 * @group dao
 * @group mysql
 * @group sqlite
 * @group postgresql
 */
class DuplicateObjectExceptionTest extends TestCaseDAO
{	
	/**
	 * @dataProvider dbConnections
	 */
	public function testInsert($db)
	{
		DBPool::me()->setDefault($db);
		
		DBPool::me()->getLink()->queryRaw("CREATE UNIQUE INDEX uq_name ON custom_table (name)");
		
		$moscow	= TestCity::create()->setName('Moscow');
		$perm	= TestCity::create()->setName('Moscow'); 
		
		$moscow = $moscow->dao()->add($moscow);
		
		try {
		    $perm = $perm->dao()->add($perm);
		} catch (DatabaseException $e) {
		    $perm = $perm->dao()->add($perm->setName('Perm'));
		}
		
		$this->assertIsNumeric($perm->getId());
	}
	
	public function dbConnections()
	{
		return array(
			array($this->getDbByType(MySQLim::class)),
			array($this->getDbByType(PgSQL::class)),
			array($this->getDbByType(SQLitePDO::class))
		);
	}
}
?>