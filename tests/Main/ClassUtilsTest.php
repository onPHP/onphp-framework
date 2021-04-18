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
use OnPHP\Tests\Meta\Business\TestObject;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestAbstract;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestClass;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestClassChild;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestClassLazyLoad;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestInterface;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestTrait;
use OnPHP\Tests\TestEnvironment\ClassUtils\TestTraitChild;
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

		try {
			ClassUtils::callStaticMethod([]);
			$this->fail('WrongArgumentException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			ClassUtils::callStaticMethod(false);
			$this->fail('WrongArgumentException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			ClassUtils::callStaticMethod(function() { return true; });
			$this->fail('WrongArgumentException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			ClassUtils::callStaticMethod(555);
			$this->fail('WrongArgumentException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			ClassUtils::callStaticMethod(555.55);
			$this->fail('WrongArgumentException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			ClassUtils::callStaticMethod(new \DateTime());
			$this->fail('WrongArgumentException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
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

	public function testCopyProperties()
	{
		$source = TestClass::create();
		$destination = TestClass::create()->setText('old Text');

		ClassUtils::copyProperties($source, $destination);
		$this->assertEquals($source->getText(), $destination->getText());
		$this->assertEquals($source->getObject(), $destination->getObject());
		$this->assertNull($destination->getText());
		$this->assertNull($destination->getObject());

		$destination
			->setText('old text')
			->setObject(
				TestClass::create()
					->setText('old inner object text')
			);
		ClassUtils::copyProperties($source, $destination);
		$this->assertEquals($source->getText(), $destination->getText());
		$this->assertEquals($source->getObject(), $destination->getObject());
		$this->assertNull($destination->getText());
		$this->assertNull($destination->getObject());

		$source
			->setText('source text')
			->setObject(
				TestClass::create()
					->setText('source inner text')
					->setObject(
						TestClass::create()->setText('double inner source text')
					)
			);
		ClassUtils::copyProperties($source, $destination);
		$this->assertNotNull($destination->getText());
		$this->assertNotNull($destination->getObject());
		$this->assertNotNull($destination->getObject()->getObject());
		$this->assertEquals($source->getText(), $destination->getText());
		$this->assertEquals($source->getObject(), $destination->getObject());
		$this->assertEquals($source->getObject()->getText(), $destination->getObject()->getText());
		$this->assertEquals('source inner text', $destination->getObject()->getText());
		$this->assertEquals($source->getObject()->getObject(), $destination->getObject()->getObject());
		$this->assertEquals('double inner source text', $destination->getObject()->getObject()->getText());
		$this->assertEquals(
			$source->getObject()->getObject()->getText(),
			$destination->getObject()->getObject()->getText()
		);

		$source = TestClassLazyLoad::create();
		$source->name = 'name';
		$destination = TestClassLazyLoad::create()->setText('old Text');

		ClassUtils::copyProperties($source, $destination);
		$this->assertNotEquals($source->getText(), $destination->getText());
		$this->assertEquals('old Text', $destination->getText());
		$this->assertNotEquals($source->name, $destination->name);
		$this->assertNull($destination->name);

		$source->setText('source text')->setObject(TestClass::create());
		ClassUtils::copyProperties($source, $destination);
		$this->assertNotEquals($source->getText(), $destination->getText());
		$this->assertEquals('old Text', $destination->getText());
		$this->assertNull($destination->getObject());

		$source->setTestId(1);
		ClassUtils::copyProperties($source, $destination);
		$this->assertEquals($source->getTestId(), $destination->getTestId());
		$this->assertNotNull($source->getTest());
		$this->assertNotNull($destination->getTest());
		$this->assertEquals($source->getTest(), $destination->getTest());

		$source->dropTest();
		ClassUtils::copyProperties($source, $destination);
		$this->assertEquals($source->getTestId(), $destination->getTestId());
		$this->assertNull($source->getTest());
		$this->assertNull($destination->getTest());
	}

	public function testClassImplements()
	{
		$interfaces = ClassUtils::isClassImplements(TestClassLazyLoad::class);
		$interfacesParent = ClassUtils::isClassImplements(TestClass::class);
		$this->assertIsArray($interfaces);
		$this->assertIsArray($interfacesParent);
		$this->assertEquals($interfaces, $interfacesParent);
		$this->assertArrayHasKey(TestInterface::class, $interfaces);
		$this->assertArrayHasKey(TestInterface::class, $interfacesParent);
		$this->assertIsArray(ClassUtils::isClassImplements(TestInterface::class));
		$this->assertEmpty(ClassUtils::isClassImplements(TestInterface::class));
		$this->assertIsArray(ClassUtils::isClassImplements(TestTrait::class));
		$this->assertEmpty(ClassUtils::isClassImplements(TestTrait::class));
		$this->assertIsArray(ClassUtils::isClassImplements(function () { return true; }));
		$this->assertEmpty(ClassUtils::isClassImplements(function () { return true; }));
		$this->assertArrayHasKey(
			\DateTimeInterface::class,
			ClassUtils::isClassImplements(\DateTime::class)
		);

		try {
			ClassUtils::isClassImplements('\SomeUnexistedClass');
			$this->fail('ClassNotFoundException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(ClassNotFoundException::class, $exception);
		}
	}

	public function testInstanceOf()
	{
		$this->assertFalse(ClassUtils::isInstanceOf('2007-07-14&genre', 'Date'));
		$this->assertTrue(ClassUtils::isInstanceOf(new \DateTime(), 'DateTimeInterface'));
		$this->assertTrue(ClassUtils::isInstanceOf(new \DateTime(), '\DateTimeInterface'));
		$this->assertTrue(ClassUtils::isInstanceOf(new \DateTime(), 'DateTime'));
		$this->assertTrue(ClassUtils::isInstanceOf(new \DateTime(), '\DateTime'));
		$this->assertFalse(ClassUtils::isInstanceOf(TestClass::class, function() { return true; }));
		$this->assertTrue(ClassUtils::isInstanceOf('\DateTime', '\DateTimeInterface'));
		$this->assertTrue(ClassUtils::isInstanceOf('\DateTime', 'DateTimeInterface'));
		$this->assertTrue(ClassUtils::isInstanceOf('DateTime', '\DateTimeInterface'));
		$this->assertTrue(ClassUtils::isInstanceOf('DateTime', 'DateTimeInterface'));
		$this->assertTrue(ClassUtils::isInstanceOf(function () { return true; }, '\Closure'));
		$this->assertTrue(ClassUtils::isInstanceOf(function () { return true; }, \Closure::class));
		try {
			ClassUtils::isInstanceOf(TestClass::class, true);
			$this->fail('WrongArgumentException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			ClassUtils::isInstanceOf(false, \Closure::class);
			$this->fail('WrongArgumentException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			$this->assertFalse(ClassUtils::isInstanceOf(TestClass::class, []));
			$this->fail('WrongArgumentException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			$this->assertFalse(ClassUtils::isInstanceOf([],TestClass::class));
			$this->fail('WrongArgumentException expected');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$this->assertTrue(ClassUtils::isInstanceOf(TestClassChild::class, TestClass::class));
		$this->assertFalse(ClassUtils::isInstanceOf(TestClass::class, TestClassChild::class));
		$this->assertTrue(ClassUtils::isInstanceOf(TestClassChild::class, TestInterface::class));
		$this->assertTrue(ClassUtils::isInstanceOf(TestClass::class, TestInterface::class));
		$this->assertTrue(ClassUtils::isInstanceOf(TestAbstract::class, TestInterface::class));
		$this->assertTrue(ClassUtils::isInstanceOf(TestAbstract::class, TestClass::class));
		$this->assertFalse(ClassUtils::isInstanceOf(TestAbstract::class, TestClassChild::class));

		$this->assertFalse(ClassUtils::isInstanceOf(TestClass::class, TestTrait::class));
		$this->assertFalse(ClassUtils::isInstanceOf(TestClass::class, TestTraitChild::class));
		$this->assertFalse(ClassUtils::isInstanceOf(TestClassChild::class, TestTrait::class));
		$this->assertFalse(ClassUtils::isInstanceOf(TestClassChild::class, TestTraitChild::class));

		$base = new TestClass;
		$this->assertTrue(ClassUtils::isInstanceOf($base, $base));
		$this->assertFalse(ClassUtils::isInstanceOf($base, TestTrait::class));
		$this->assertFalse(ClassUtils::isInstanceOf($base, TestTraitChild::class));

		$this->assertTrue(ClassUtils::isInstanceOf(TestAbstract::class, $base));
		$this->assertFalse(ClassUtils::isInstanceOf($base, TestAbstract::class));

		$child = new TestClassChild();

		$this->assertFalse(ClassUtils::isInstanceOf($base, $child));
		$this->assertTrue(ClassUtils::isInstanceOf($child, $base));

		$this->assertFalse(ClassUtils::isInstanceOf($base, TestClassChild::class));
		$this->assertTrue(ClassUtils::isInstanceOf($child, TestClass::class));

		$this->assertFalse(ClassUtils::isInstanceOf($child, TestTrait::class));
		$this->assertFalse(ClassUtils::isInstanceOf($child, TestTraitChild::class));

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
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		try {
			ClassUtils::isSameClassNames('\Some\Namespace\FakeClassName', TestClass::class);
			$this->fail('WrongArgumentException expected');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
	}

	public function testSameClassNames()
	{
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.TestClass::class, TestClass::class));
		$this->assertTrue(ClassUtils::isSameClassNames(TestClass::class, '\\'.TestClass::class));
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.TestClass::class, '\\'.TestClass::class));
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.TestClass::class, '\\'.TestClass::class));
		
		$this->assertTrue(ClassUtils::isSameClassNames('\\'.TestClass::class, new TestClass));
		$this->assertFalse(ClassUtils::isSameClassNames('\\'.TestClassChild::class, new TestClass()));
		$this->assertFalse(ClassUtils::isSameClassNames(new TestClass(), '\\'.TestClassChild::class));

		$this->assertTrue(ClassUtils::isSameClassNames(function() { return true; }, 'Closure'));
		$this->assertTrue(ClassUtils::isSameClassNames(function() { return true; }, '\Closure'));
		$this->assertTrue(ClassUtils::isSameClassNames('Closure', function() { return true; }));
		$this->assertTrue(ClassUtils::isSameClassNames('\Closure', function() { return true; }));
		$this->assertTrue(ClassUtils::isSameClassNames('\DateTime', 'DateTime'));
		$this->assertTrue(ClassUtils::isSameClassNames('DateTime', '\DateTime'));
		$this->assertTrue(ClassUtils::isSameClassNames('\DateTime', '\DateTime'));
		$this->assertTrue(ClassUtils::isSameClassNames('DateTime', 'DateTime'));

		try {
			ClassUtils::isSameClassNames(TestTrait::class, TestTrait::class);
			$this->fail('WrongArgumentException expected');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		try {
			ClassUtils::isSameClassNames('\Some\Namespace\FakeClassName',TestClass::class);
			$this->fail('WrongArgumentException expected');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			ClassUtils::isSameClassNames(123,TestClass::class);
			$this->fail('WrongArgumentException expected');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
	}
	
	public function testIsClassName()
	{
		$this->assertFalse(ClassUtils::isClassName(null));
		$this->assertFalse(ClassUtils::isClassName([]));
		$this->assertFalse(ClassUtils::isClassName(false));
		$this->assertFalse(ClassUtils::isClassName(function() { return true; }));
		$this->assertFalse(ClassUtils::isClassName(''));
		$this->assertFalse(ClassUtils::isClassName(0));
		$this->assertFalse(ClassUtils::isClassName('0'));
		$this->assertTrue(ClassUtils::isClassName('A0'));
		$this->assertTrue(ClassUtils::isClassName('_'));
		$this->assertTrue(ClassUtils::isClassName('_1'));
		$this->assertTrue(ClassUtils::isClassName('Correct_Class1'));
	}
}