<?php

	final class DbConnectionTest extends TestCase
	{
		public function setUp()
		{
			DBPool::me()->
				addLink(
					'badLink',
					DB::spawn('PinbedPgSQL', 'postgres', '', 'localhost', 'wrongDatabase')
				);
		}
		
		public function testPostgresql()
		{
			try {
				$link = DBPool::me()->getLink('badLink');
				$this->assertFalse(true, 'Unreachable code');
			} catch(Exception $e) {
				$this->assertInstanceOf('DatabaseException', $e);
			}
		}
		
		public function tearDown()
		{
			DBPool::me()->dropLink('badLink');
		}
	}

?>