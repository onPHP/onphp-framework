<?php
/*$Id$*/

class PgSQLTest extends UnitTestCase
{
	
    function __construct()
	{
        $this->UnitTestCase();
    }
    
	function setUp()
	{
		//
	}
	
	function testQuoteTable()
	{
		$this->assertEqual('"ShemaName"."TableName"', PgSQL::quoteTable($temp = 'ShemaName.TableName'));
		$this->assertEqual('"TableName"', PgSQL::quoteTable($temp = 'TableName'));
	}
		
}

?>
