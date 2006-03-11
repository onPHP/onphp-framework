<?php
	/* $Id$ */
	
	final class SingletonTestInstance extends Singleton {/*_*/}
	
	final class SingletonTest extends UnitTestCase
	{
		private $childName = 'SingletonTestInstance';
		
		public function testFactoryLikeCall()
		{
			$this->assertTrue(
				Singleton::getInstance($this->childName)
				=== Singleton::getInstance($this->childName)
			);
		}
		
		public function testOverloadedCall()
		{
			$name = $this->childName;
			
			$this->assertTrue(
				Singleton::getInstance()->$name()
				=== Singleton::getInstance()->$name()
			);
		}
		
		public function testCreationProhibition()
		{
			$child = new ReflectionClass($this->childName);
			
			$this->assertTrue(
				!$child->getMethod('__construct')->isPublic()
			);
			
			$this->assertTrue(
				$child->getMethod('__construct')->isProtected()
			);
		}
	}
?>