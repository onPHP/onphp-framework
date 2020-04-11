<?php
	/* $Id$ */
	
	final class CookieTest extends TestCase
	{
		/**
		* @expectedException WrongStateException
		*/
		public function testCookie()
		{
			if (!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
				$this->markTestSkipped('can\'t test cookies without web');

			echo "\0";
			
			Cookie::create('testCookie')->
				setValue('testValue')->
				setMaxAge(60*60)->
				httpSet();
		}
		
		/**
		* @expectedException WrongStateException
		*/
		public function testCookieCollection()
		{
			if (!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
				$this->markTestSkipped('can\'t test cookies without web');
			
			echo "\0";
			
			CookieCollection::create()->
				add(
					Cookie::create('anotherTestCookie')->
						setValue('testValue')->
						setMaxAge(60*60)
				)->
				httpSetAll();
		}
	}
?>