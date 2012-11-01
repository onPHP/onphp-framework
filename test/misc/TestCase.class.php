<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	abstract class TestCase extends \PHPUnit_Framework_TestCase
	{
		protected $backupGlobals = false;
	}
?>