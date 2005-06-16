<?php
/*
	Copyright 2005 Sveta Smirnova
	$Id$
*/

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.inc.php';
import('OSQL');

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'mysql.inc.php';

class MySQLTestCase extends UnitTestCase {
	
	private $db;
	
	function __construct()
	{
		$this->UnitTestCase('MySQL test');
	}
	
	function setUP()
	{
		$this->db = new MySQL();
		$this->db->connect(TEST_USER, TEST_PASS, TEST_HOST, TEST_BASE, true);
	}
	
	function testConnect()
	{
		$this->assertTrue($this->db->isConnected());
	}
	
	function testQuery()
	{
		$this->assertEqual(array(1), $this->db->query(OSQL::select()->
													  from('pets')->get('*')->
													  where(Expression::eq('sex', 'm'))));
	}
	
}

$test = &new MySQLTestCase();
$test->run(new TextReporter());

?>
