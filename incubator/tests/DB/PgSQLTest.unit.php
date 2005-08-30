<?php
/*$Id: Logic.unit.php 116 2005-07-25 20:08:58Z ssmirnova $*/

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
