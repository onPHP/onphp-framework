<?php
	/* $Id$ */
	
	final class PostgresDialectTest extends TestCase
	{
		public function testPrepareFullText()
		{
			$this->assertEquals(
				PostgresDialect::prepareFullText(
					array('Новый год', 'Снегурочка', 'ПрАзДнИк'),
					DB::FULL_TEXT_AND),
				"'новый год' & 'снегурочка' & 'праздник'"
			);
		}
	}
?>