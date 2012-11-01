<?php
	/* $Id$ */

	namespace Onphp\Test;

	final class ClassUtilsTest extends TestCase
	{
		public function testStaticMethodCalling()
		{
			$this->assertEquals(
				\Onphp\ClassUtils::callStaticMethod(
					'\Onphp\Singleton::getInstance',
					'\Onphp\UrlEncodeFilter'
				),
				
				\Onphp\Singleton::getInstance('\Onphp\UrlEncodeFilter')
			);
			
			$this->assertEquals(
				\Onphp\ClassUtils::callStaticMethod('\Onphp\ImaginaryDialect::me'),
				\Onphp\ImaginaryDialect::me()
			);
			
			try {
				\Onphp\ClassUtils::callStaticMethod('InexistantClass::InSaNeMeThOd');
				$this->fail();
			} catch (\Onphp\ClassNotFoundException $e) {
				/* first pass */
			} catch (\Onphp\WrongArgumentException $e) {
				/* and all others */
			}
			
			try {
				\Onphp\ClassUtils::callStaticMethod('complete nonsense');
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {
				/* pass */
			}
			
			try {
				\Onphp\ClassUtils::callStaticMethod('Identifier::comp::lete::non::sense');
				$this->fail();
			} catch (\Onphp\ClassNotFoundException $e) {
				/* pass */
			}
		}
		
		public function testSet()
		{
			$source =
				ClassUtilsTestClass::create()->
				setText('new Text');
			
			$destination =
				ClassUtilsTestClass::create()->
				setText('old Text');
			
			\Onphp\ClassUtils::fillNullProperties($source, $destination);
			$this->assertEquals($destination->getText(), 'old Text');

			\Onphp\ClassUtils::copyNotNullProperties($source, $destination);
			$this->assertEquals($destination->getText(), 'new Text');
		}
		
		public function testNotSet()
		{
			$source = ClassUtilsTestClass::create();
				
			$destination =
				ClassUtilsTestClass::create()->
				setText('old Text');			
			
			\Onphp\ClassUtils::fillNullProperties($source, $destination);
			$this->assertEquals($destination->getText(), 'old Text');
			
			\Onphp\ClassUtils::copyNotNullProperties($source, $destination);
			$this->assertEquals($destination->getText(), 'old Text');
		}

		public function testObject()
		{
			$innerObject =
				ClassUtilsTestClass::create()->
				setText('inner Object');
			
			$source =
				ClassUtilsTestClass::create()->
				setObject($innerObject);
				
			$destination =
				ClassUtilsTestClass::create()->
				setText('old Text');			
			
			\Onphp\ClassUtils::fillNullProperties($source, $destination);
			
			$this->assertTrue($destination->getObject() === $innerObject);
			
			$destination->dropObject();
			
			\Onphp\ClassUtils::copyNotNullProperties($source, $destination);
			$this->assertTrue($destination->getObject() === $innerObject);
		}
		
		public function testInstanceOf()
		{
			try {
				$this->assertFalse(\Onphp\ClassUtils::isInstanceOf('2007-07-14&genre', '\Onphp\Date'));
			} catch (\Onphp\WrongArgumentException $e) {
				/* pass */
			}
			$this->assertTrue(\Onphp\ClassUtils::isInstanceOf('\Onphp\Test\ClassUtilsTestClassChild', '\Onphp\Test\ClassUtilsTestClass'));
			$this->assertFalse(\Onphp\ClassUtils::isInstanceOf('\Onphp\Test\ClassUtilsTestClass', '\Onphp\Test\ClassUtilsTestClassChild'));
			$this->assertTrue(\Onphp\ClassUtils::isInstanceOf('\Onphp\Test\ClassUtilsTestClassChild', '\Onphp\Test\ClassUtilsTestInterface'));
			$this->assertTrue(\Onphp\ClassUtils::isInstanceOf('\Onphp\Test\ClassUtilsTestClass', '\Onphp\Test\ClassUtilsTestInterface'));
			$this->assertTrue(\Onphp\ClassUtils::isInstanceOf('\Onphp\Test\ClassUtilsTestAbstract', '\Onphp\Test\ClassUtilsTestInterface'));
			$this->assertTrue(\Onphp\ClassUtils::isInstanceOf('\Onphp\Test\ClassUtilsTestAbstract', '\Onphp\Test\ClassUtilsTestClass'));
			$this->assertFalse(\Onphp\ClassUtils::isInstanceOf('\Onphp\Test\ClassUtilsTestAbstract', '\Onphp\Test\ClassUtilsTestClassChild'));
			
			$base = new ClassUtilsTestClass;
			$this->assertTrue(\Onphp\ClassUtils::isInstanceOf($base, $base));
			
			$this->assertTrue(\Onphp\ClassUtils::isInstanceOf('\Onphp\Test\ClassUtilsTestAbstract', $base));
			$this->assertFalse(\Onphp\ClassUtils::isInstanceOf($base, '\Onphp\Test\ClassUtilsTestAbstract'));
			
			$child = new ClassUtilsTestClassChild();
			
			$this->assertFalse(\Onphp\ClassUtils::isInstanceOf($base, $child));
			$this->assertTrue(\Onphp\ClassUtils::isInstanceOf($child, $base));
			
			$this->assertFalse(\Onphp\ClassUtils::isInstanceOf($base, '\Onphp\Test\ClassUtilsTestClassChild'));
			$this->assertTrue(\Onphp\ClassUtils::isInstanceOf($child, '\Onphp\Test\ClassUtilsTestClass'));
		}
		
		public function testIsClassName()
		{
			$this->assertFalse(\Onphp\ClassUtils::isClassName(null));
			$this->assertFalse(\Onphp\ClassUtils::isClassName(''));
			$this->assertFalse(\Onphp\ClassUtils::isClassName(0));
			$this->assertFalse(\Onphp\ClassUtils::isClassName('0'));
			$this->assertTrue(\Onphp\ClassUtils::isClassName('A0'));
			$this->assertTrue(\Onphp\ClassUtils::isClassName('_'));
			$this->assertTrue(\Onphp\ClassUtils::isClassName('_1'));
			$this->assertTrue(\Onphp\ClassUtils::isClassName('Correct_Class1'));
		}
	}
	
	interface ClassUtilsTestInterface {}
	
	class ClassUtilsTestClass implements ClassUtilsTestInterface
	{
		private $object	= null;
		private $text 	= null;
		
		public static function create()
		{
			return new self;
		}

		public function getObject()
		{
			return $this->object;
		}
		
		public function setObject(ClassUtilsTestClass $object)
		{
			$this->object = $object;
			
			return $this;
		}
		
		public function dropObject()
		{
			$this->object = null;
			
			return $this;
		}
		
		public function getText()
		{
			return $this->text;
		}
		
		public function setText($text)
		{
			$this->text = $text;
			
			return $this;
		}
	}
	
	class ClassUtilsTestClassChild extends ClassUtilsTestClass { };
	
	abstract class ClassUtilsTestAbstract extends ClassUtilsTestClass { };
?>