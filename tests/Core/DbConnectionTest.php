<?php

namespace OnPHP\Tests\Core;

use OnPHP\Core\DB\DB;
use OnPHP\Core\DB\DBPool;
use OnPHP\Core\Exception\DatabaseException;
use OnPHP\Main\Monitoring\PinbedPgSQL;
use OnPHP\Tests\TestEnvironment\TestCase;

final class DbConnectionTest extends TestCase
{
	public function setUp(): void
	{
		DBPool::me()->
			addLink(
				'badLink',
				DB::spawn(PinbedPgSQL::class, 'postgres', '', 'localhost', 'wrongDatabase')
			);
	}
	
	public function testPostgresql()
	{
		try {
			DBPool::me()->getLink('badLink');
			$this->fail('Unreachable code');
		} catch(\Exception $e) {
			$this->assertInstanceOf(DatabaseException::class, $e);
		}
	}
	
	public function tearDown(): void
	{
		DBPool::me()->dropLink('badLink');
	}
}

?>