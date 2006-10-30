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
		
		public function testNonSingletonChilds()
		{
			try {
				Singleton::getInstance('Ternary');
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
		}
		
		public function testCreationProhibition()
		{
			$child = new ReflectionClass($this->childName);
			
			$this->assertFalse(
				$child->getMethod('__construct')->isPublic()
			);
			
			$this->assertTrue(
				$child->getMethod('__construct')->isProtected()
			);
		}
	}
?>