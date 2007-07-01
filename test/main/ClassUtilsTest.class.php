<?php
	/* $Id$ */

	final class ClassUtilsTest extends UnitTestCase
	{
		public function testStaticMethodCalling()
		{
			$this->assertEqual(
				ClassUtils::callStaticMethod(
					'Singleton::getInstance',
					'UrlEncodeFilter'
				),
				
				Singleton::getInstance('UrlEncodeFilter')
			);
			
			$this->assertEqual(
				ClassUtils::callStaticMethod('ImaginaryDialect::me'),
				ImaginaryDialect::me()
			);
			
			try {
				// in real life it will be only one exception
				$this->expectError(
					'include(InexistantClass.class.php): failed to open stream:'
					.' No such file or directory'
				);
				
				$this->expectError(
					"include(): Failed opening 'InexistantClass.class.php' for "
					."inclusion (include_path='".get_include_path()."')"
				);
				
				ClassUtils::callStaticMethod('InexistantClass::InSaNeMeThOd');
				$this->fail();
			} catch (ClassNotFoundException $e) {
				$this->pass();
			}
			
			try {
				ClassUtils::callStaticMethod('complete nonsense');
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
			
			try {
				ClassUtils::callStaticMethod('Identifier::comp::lete::non::sense');
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
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
			
			ClassUtils::fillNullProperties($source, $destination);
			$this->assertEqual($destination->getText(), 'old Text');

			ClassUtils::copyNotNullProperties($source, $destination);
			$this->assertEqual($destination->getText(), 'new Text');
		}
		
		public function testNotSet()
		{
			$source = ClassUtilsTestClass::create();
				
			$destination =
				ClassUtilsTestClass::create()->
				setText('old Text');			
			
			ClassUtils::fillNullProperties($source, $destination);
			$this->assertEqual($destination->getText(), 'old Text');
			
			ClassUtils::copyNotNullProperties($source, $destination);
			$this->assertEqual($destination->getText(), 'old Text');
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
			
			ClassUtils::fillNullProperties($source, $destination);
			
			$this->assertTrue($destination->getObject() === $innerObject);
			
			$destination->dropObject();
			
			ClassUtils::copyNotNullProperties($source, $destination);
			$this->assertTrue($destination->getObject() === $innerObject);
		}
	}
	
	class ClassUtilsTestClass
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
?>