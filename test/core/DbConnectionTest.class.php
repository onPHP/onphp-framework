<?php

	namespace Onphp\Test;

	final class DbConnectionTest extends TestCase
	{
		public function setUp()
		{
			\Onphp\DBPool::me()->
				addLink(
					'badLink',
					\Onphp\DB::spawn('\Onphp\PinbedPgSQL', 'postgres', '', 'localhost', 'wrongDatabase')
				);
		}
		
		public function testPostgresql()
		{
			try {
				$link = \Onphp\DBPool::me()->getLink('badLink');
				$this->fail('Unreachable code');
			} catch(\Exception $e) {
				$this->assertInstanceOf('\Onphp\DatabaseException', $e);
			}
		}
		
		public function tearDown()
		{
			\Onphp\DBPool::me()->dropLink('badLink');
		}
	}

?>