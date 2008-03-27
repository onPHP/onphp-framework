<?php
	/* $Id$ */
	
	final class SingletonTestInstance extends Singleton {/*_*/}
	
	final class SingletonTest extends TestCase
	{
		private $childName = 'SingletonTestInstance';
		
		public function testFactoryLikeCall()
		{
			$this->assertTrue(
				Singleton::getInstance($this->childName)
				=== Singleton::getInstance($this->childName)
			);
			
			$all = Singleton::getAllInstances();
			
			$this->assertTrue(
				Singleton::getInstance($this->childName)
				=== $all[$this->childName]
			);
		}
		
		public function testNonSingletonChilds()
		{
			try {
				Singleton::getInstance('Ternary');
				$this->fail();
			} catch (WrongArgumentException $e) {
				/* pass */
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
			
			$this->assertTrue(
				$child->getMethod('__sleep')->isFinal()
			);
			$this->assertTrue(
				$child->getMethod('__sleep')->isPrivate()
			);
			
			$this->assertTrue(
				$child->getMethod('__clone')->isFinal()
			);
			$this->assertTrue(
				$child->getMethod('__clone')->isPrivate()
			);
		}
	}
?>