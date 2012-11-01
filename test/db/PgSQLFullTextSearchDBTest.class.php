<?php
	/**
	 * @group pgsf
	 */
	namespace Onphp\Test;

	class PgSQLFullTextSearchDBTest extends TestCaseDB
	{
		/**
		 * @test
		 */
		public function getPgSQL()
		{
			foreach (DBTestPool::me()->getPool() as $db)
				if ($db instanceof \Onphp\PgSQL)
					return $db;
				
			$this->markTestIncomplete('Required PgSQL for testing');
		}
		
		/**
		 * @depends getPgSQL
		 * @return \Onphp\PgSQL
		 */
		public function testPrepairFullText(\Onphp\PgSQL $db)
		{
			$this->assertEquals(
				"'новый год' & 'снегурочка' & 'праздник'",
				$db->getDialect()->prepareFullText(
					array('Новый год', 'Снегурочка', 'ПрАзДнИк'),
					DB::FULL_TEXT_AND
				)
			);
		}
	}
?>