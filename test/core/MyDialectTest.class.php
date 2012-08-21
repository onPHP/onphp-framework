<?php
	/**
	 * @author Artem Naumenko <a.naumenko@co.wapstart.ru>
	 * @copyright Copyright (c) 2012, Wapstart
	 */
	final class MyDialectTest extends TestCase
	{
		public function setUp()
		{
			if (!extension_loaded('mysql'))
				$this->markTestSkipped('Install mysql extension');
			
			try {
				$link = DB::spawn('MySQL', 'root', '', 'localhost');
				$link->connect();
				DBPool::me()->addLink('test', $link);
			} catch (Exception $e) {
				$this->markTestSkipped("Can't connect to MySQL on localhost");
			}
		}
		
		public function testCastTo()
		{
			$result =
				DBPool::me()->getLink('test')->queryRow(
					OSQL::select()->
					get(
						DBValue::create('12')->
						castTo('decimal(5, 3)'),
						'test'
					)
				);
			
			$this->assertEquals($result['test'], '12.000');
		}
	}
