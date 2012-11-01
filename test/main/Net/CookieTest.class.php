<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class CookieTest extends TestCase
	{
		/**
		* @expectedException \Onphp\WrongStateException
		*/
		public function testCookie()
		{
			if (!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
				$this->markTestSkipped('can\'t test cookies without web');

			echo "\0";
			
			\Onphp\Cookie::create('testCookie')->
				setValue('testValue')->
				setMaxAge(60*60)->
				httpSet();
		}
		
		/**
		* @expectedException \Onphp\WrongStateException
		*/
		public function testCookieCollection()
		{
			if (!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
				$this->markTestSkipped('can\'t test cookies without web');
			
			echo "\0";
			
			\Onphp\CookieCollection::create()->
				add(
					\Onphp\Cookie::create('anotherTestCookie')->
						setValue('testValue')->
						setMaxAge(60*60)
				)->
				httpSetAll();
		}
	}
?>