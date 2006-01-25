<?php
	/* $Id$ */
	
	final class SingletoneTestInstance extends Singletone {/*_*/}
	
	final class SingletoneTest extends UnitTestCase
	{
		private $childName = 'SingletoneTestInstance';
		
		public function testFactoryLikeCall()
		{
			$this->assertTrue(
				Singletone::getInstance($this->childName)
				=== Singletone::getInstance($this->childName)
			);
		}
		
		public function testOverloadedCall()
		{
			$name = $this->childName;
			
			$this->assertTrue(
				Singletone::getInstance()->$name()
				=== Singletone::getInstance()->$name()
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