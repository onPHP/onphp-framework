<?php

namespace OnPHP\Tests\Main;

use OnPHP\Core\Base\Identifier;
use OnPHP\Core\Base\Singleton;
use OnPHP\Core\DB\ImaginaryDialect;
use OnPHP\Core\Exception\ClassNotFoundException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Form\Filters\UrlEncodeFilter;
use OnPHP\Main\Util\ClassUtils;
use OnPHP\Tests\TestEnvironment\ClassUtilsTestAbstract;
use OnPHP\Tests\TestEnvironment\ClassUtilsTestClass;
use OnPHP\Tests\TestEnvironment\ClassUtilsTestClassChild;
use OnPHP\Tests\TestEnvironment\ClassUtilsTestInterface;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group main
 */
final class ClassUtilsTest extends TestCase
{
	
	public function testOldStypeStaticMethodCall()
	{
		$this->assertEquals(
			ClassUtils::callStaticMethod(
				Singleton::class.'::getInstance',
				UrlEncodeFilter::class
			),

			Singleton::getInstance(UrlEncodeFilter::class)
		);
		
		$this->assertEquals(
			ClassUtils::callStaticMethod(ImaginaryDialect::class.'::me'),
			ImaginaryDialect::me()
		);
	}
	
	public function testStaticMethodCall()
	{
		$this->assertEquals(
			ClassUtils::callStaticMethod(
				[Singleton::class, 'getInstance'],
				UrlEncodeFilter::class
			),

			Singleton::getInstance(UrlEncodeFilter::class)
		);
		
		$this->assertEquals(
			ClassUtils::callStaticMethod(ImaginaryDialect::class.'::me'),
			ImaginaryDialect::me()
		);
	}
	
	public function testInexistentStaticMethodCall() {
		$this->expectException(ClassNotFoundException::class);
		ClassUtils::callStaticMethod('InexistantClass::InSaNeMeThOd');
	}
	
	public function testInexistentMultiplyStaticMethodCall()
	{
		$this->expectException(WrongArgumentException::class);
		ClassUtils::callStaticMethod(Identifier::class.'::comp::lete::non::sense');
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
		$this->assertEquals($destination->getText(), 'old Text');

		ClassUtils::copyNotNullProperties($source, $destination);
		$this->assertEquals($destination->getText(), 'new Text');
	}

	public function testNotSet()
	{
		$source = ClassUtilsTestClass::create();

		$destination =
			ClassUtilsTestClass::create()->
			setText('old Text');			

		ClassUtils::fillNullProperties($source, $destination);
		$this->assertEquals($destination->getText(), 'old Text');

		ClassUtils::copyNotNullProperties($source, $destination);
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

		ClassUtils::fillNullProperties($source, $destination);

		$this->assertTrue($destination->getObject() === $innerObject);

		$destination->dropObject();

		ClassUtils::copyNotNullProperties($source, $destination);
		$this->assertTrue($destination->getObject() === $innerObject);
	}

	public function testInstanceOf()
	{
		try {
			$this->assertFalse(ClassUtils::isInstanceOf('2007-07-14&genre', 'Date'));
		} catch (WrongArgumentException $e) {
			/* pass */
		}
		$this->assertTrue(ClassUtils::isInstanceOf(ClassUtilsTestClassChild::class, ClassUtilsTestClass::class));
		$this->assertFalse(ClassUtils::isInstanceOf(ClassUtilsTestClass::class, ClassUtilsTestClassChild::class));
		$this->assertTrue(ClassUtils::isInstanceOf(ClassUtilsTestClassChild::class, ClassUtilsTestInterface::class));
		$this->assertTrue(ClassUtils::isInstanceOf(ClassUtilsTestClass::class, ClassUtilsTestInterface::class));
		$this->assertTrue(ClassUtils::isInstanceOf(ClassUtilsTestAbstract::class, ClassUtilsTestInterface::class));
		$this->assertTrue(ClassUtils::isInstanceOf(ClassUtilsTestAbstract::class, ClassUtilsTestClass::class));
		$this->assertFalse(ClassUtils::isInstanceOf(ClassUtilsTestAbstract::class, ClassUtilsTestClassChild::class));

		$base = new ClassUtilsTestClass;
		$this->assertTrue(ClassUtils::isInstanceOf($base, $base));

		$this->assertTrue(ClassUtils::isInstanceOf(ClassUtilsTestAbstract::class, $base));
		$this->assertFalse(ClassUtils::isInstanceOf($base, ClassUtilsTestAbstract::class));

		$child = new ClassUtilsTestClassChild();

		$this->assertFalse(ClassUtils::isInstanceOf($base, $child));
		$this->assertTrue(ClassUtils::isInstanceOf($child, $base));

		$this->assertFalse(ClassUtils::isInstanceOf($base, ClassUtilsTestClassChild::class));
		$this->assertTrue(ClassUtils::isInstanceOf($child, ClassUtilsTestClass::class));
		
		$this->assertTrue(ClassUtils::isInstanceOf('\\'.ClassUtilsTestClass::class, ClassUtilsTestClass::class));
		$this->assertTrue(ClassUtils::isInstanceOf(ClassUtilsTestClass::class, '\\'.ClassUtilsTestClass::class));
		$this->assertTrue(ClassUtils::isInstanceOf('\\'.ClassUtilsTestClass::class, '\\'.ClassUtilsTestClass::class));
		$this->assertTrue(ClassUtils::isInstanceOf('\\'.ClassUtilsTestClass::class, '\\'.ClassUtilsTestClass::class));
		
		$this->assertTrue(ClassUtils::isInstanceOf('\\'.ClassUtilsTestClass::class, new ClassUtilsTestClass));
		$this->assertTrue(ClassUtils::isInstanceOf('\\'.ClassUtilsTestClassChild::class, new ClassUtilsTestClass()));
		$this->assertFalse(ClassUtils::isInstanceOf(new ClassUtilsTestClass(), '\\'.ClassUtilsTestClassChild::class));
		
		try {
			ClassUtils::isSameClassNames('ClassUtilsTestClass', ClassUtilsTestClass::class);
			$this->fail('WrongArgumentException expected');
		} catch (WrongArgumentException $e) {
			//pass
		}
		
		try {
			ClassUtils::isSameClassNames('\Some\Namespace\FakeClassName', ClassUtilsTestClass::class);
			$this->fail('WrongArgumentException expected');
		} catch (WrongArgumentException $e) {
			//pass
		}
	}

	public function testSameClassNames() {
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.ClassUtilsTestClass::class, ClassUtilsTestClass::class));
		$this->assertTrue(ClassUtils::isSameClassNames(ClassUtilsTestClass::class, '\\'.ClassUtilsTestClass::class));
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.ClassUtilsTestClass::class, '\\'.ClassUtilsTestClass::class));
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.ClassUtilsTestClass::class, '\\'.ClassUtilsTestClass::class));
		
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.ClassUtilsTestClass::class, new ClassUtilsTestClass));
		$this->assertFalse(ClassUtils::isSameClassNames('\\'.ClassUtilsTestClassChild::class, new ClassUtilsTestClass()));
		$this->assertFalse(ClassUtils::isSameClassNames(new ClassUtilsTestClass(), '\\'.ClassUtilsTestClassChild::class));
		
		try {
			$this->assertFalse(
					ClassUtils::isSameClassNames(
						'\Some\Namespace\FakeClassName',
						ClassUtilsTestClass::class
					)
				);
			$this->fail('WrongArgumentException expected');
		} catch (WrongArgumentException $e) {
			//pass
		}
		
		try {
			$this->assertFalse(
				ClassUtils::isSameClassNames(
						123,
						ClassUtilsTestClass::class
					)
				);
			$this->fail('WrongArgumentException expected');
		} catch (WrongArgumentException $e) {
			//pass
		}
	}
	
	public function testIsClassName()
	{
		$this->assertFalse(ClassUtils::isClassName(null));
		$this->assertFalse(ClassUtils::isClassName(''));
		$this->assertFalse(ClassUtils::isClassName(0));
		$this->assertFalse(ClassUtils::isClassName('0'));
		$this->assertTrue(ClassUtils::isClassName('A0'));
		$this->assertTrue(ClassUtils::isClassName('_'));
		$this->assertTrue(ClassUtils::isClassName('_1'));
		$this->assertTrue(ClassUtils::isClassName('Correct_Class1'));
	}
}
?>