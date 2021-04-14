<?php

namespace OnPHP\Tests\Main;

use OnPHP\Core\Base\Identifier;
use OnPHP\Core\Base\Singleton;
use OnPHP\Core\DB\ImaginaryDialect;
use OnPHP\Core\Exception\ClassNotFoundException;
use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Form\Filters\UrlEncodeFilter;
use OnPHP\Main\Util\ClassUtils;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestAbstract;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestClass;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestClassChild;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestInterface;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group main
 */
final class ClassUtilsTest extends TestCase
{
	/**
	 * @throws ClassNotFoundException
	 * @throws WrongArgumentException
	 * @throws MissingElementException
	 */
	public function testOldStyleStaticMethodCall()
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

	/**
	 * @throws ClassNotFoundException
	 * @throws WrongArgumentException
	 * @throws MissingElementException
	 */
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
	
	public function testInexistedStaticMethodCall()
	{
		$this->expectException(ClassNotFoundException::class);
		ClassUtils::callStaticMethod('InexistantClass::InSaNeMeThOd');
	}
	
	public function testInexistedMultiplyStaticMethodCall()
	{
		$this->expectException(WrongArgumentException::class);
		ClassUtils::callStaticMethod(Identifier::class.'::comp::lete::non::sense');
	}

	public function testSet()
	{
		$source =
			TestClass::create()->
			setText('new Text');

		$destination =
			TestClass::create()->
			setText('old Text');

		ClassUtils::fillNullProperties($source, $destination);
		$this->assertEquals($destination->getText(), 'old Text');

		ClassUtils::copyNotNullProperties($source, $destination);
		$this->assertEquals($destination->getText(), 'new Text');
	}

	public function testNotSet()
	{
		$source = TestClass::create();

		$destination =
			TestClass::create()->
			setText('old Text');			

		ClassUtils::fillNullProperties($source, $destination);
		$this->assertEquals($destination->getText(), 'old Text');

		ClassUtils::copyNotNullProperties($source, $destination);
		$this->assertEquals($destination->getText(), 'old Text');
	}

	public function testObject()
	{
		$innerObject =
			TestClass::create()->
			setText('inner Object');

		$source =
			TestClass::create()->
			setObject($innerObject);

		$destination =
			TestClass::create()->
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
		$this->assertTrue(ClassUtils::isInstanceOf(TestClassChild::class, TestClass::class));
		$this->assertFalse(ClassUtils::isInstanceOf(TestClass::class, TestClassChild::class));
		$this->assertTrue(ClassUtils::isInstanceOf(TestClassChild::class, TestInterface::class));
		$this->assertTrue(ClassUtils::isInstanceOf(TestClass::class, TestInterface::class));
		$this->assertTrue(ClassUtils::isInstanceOf(TestAbstract::class, TestInterface::class));
		$this->assertTrue(ClassUtils::isInstanceOf(TestAbstract::class, TestClass::class));
		$this->assertFalse(ClassUtils::isInstanceOf(TestAbstract::class, TestClassChild::class));

		$base = new TestClass;
		$this->assertTrue(ClassUtils::isInstanceOf($base, $base));

		$this->assertTrue(ClassUtils::isInstanceOf(TestAbstract::class, $base));
		$this->assertFalse(ClassUtils::isInstanceOf($base, TestAbstract::class));

		$child = new TestClassChild();

		$this->assertFalse(ClassUtils::isInstanceOf($base, $child));
		$this->assertTrue(ClassUtils::isInstanceOf($child, $base));

		$this->assertFalse(ClassUtils::isInstanceOf($base, TestClassChild::class));
		$this->assertTrue(ClassUtils::isInstanceOf($child, TestClass::class));
		
		$this->assertTrue(ClassUtils::isInstanceOf('\\'.TestClass::class, TestClass::class));
		$this->assertTrue(ClassUtils::isInstanceOf(TestClass::class, '\\'.TestClass::class));
		$this->assertTrue(ClassUtils::isInstanceOf('\\'.TestClass::class, '\\'.TestClass::class));
		$this->assertTrue(ClassUtils::isInstanceOf('\\'.TestClass::class, '\\'.TestClass::class));
		
		$this->assertTrue(ClassUtils::isInstanceOf('\\'.TestClass::class, new TestClass));
		$this->assertTrue(ClassUtils::isInstanceOf('\\'.TestClassChild::class, new TestClass()));
		$this->assertFalse(ClassUtils::isInstanceOf(new TestClass(), '\\'.TestClassChild::class));
		
		try {
			ClassUtils::isSameClassNames('TestClass', TestClass::class);
			$this->fail('WrongArgumentException expected');
		} catch (WrongArgumentException $e) {
			//pass
		}
		
		try {
			ClassUtils::isSameClassNames('\Some\Namespace\FakeClassName', TestClass::class);
			$this->fail('WrongArgumentException expected');
		} catch (WrongArgumentException $e) {
			//pass
		}
	}

	public function testSameClassNames() {
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.TestClass::class, TestClass::class));
		$this->assertTrue(ClassUtils::isSameClassNames(TestClass::class, '\\'.TestClass::class));
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.TestClass::class, '\\'.TestClass::class));
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.TestClass::class, '\\'.TestClass::class));
		
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.TestClass::class, new TestClass));
		$this->assertFalse(ClassUtils::isSameClassNames('\\'.TestClassChild::class, new TestClass()));
		$this->assertFalse(ClassUtils::isSameClassNames(new TestClass(), '\\'.TestClassChild::class));
		
		try {
			$this->assertFalse(
					ClassUtils::isSameClassNames(
						'\Some\Namespace\FakeClassName',
						TestClass::class
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
						TestClass::class
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