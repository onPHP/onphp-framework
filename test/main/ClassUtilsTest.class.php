<?php

/* $Id$ */

final class ClassUtilsTest extends TestCase
{
    public function testStaticMethodCalling()
    {
        $this->assertEquals(
            ClassUtils::callStaticMethod(
                'Singleton::getInstance',
                'UrlEncodeFilter'
            ),

            Singleton::getInstance('UrlEncodeFilter')
        );

        $this->assertEquals(
            ClassUtils::callStaticMethod('ImaginaryDialect::me'),
            ImaginaryDialect::me()
        );

        try {
            ClassUtils::callStaticMethod('InexistantClass::InSaNeMeThOd');
            $this->fail();
        } catch (ClassNotFoundException $e) {
            /* first pass */
        } catch (WrongArgumentException $e) {
            /* and all others */
        }

        try {
            ClassUtils::callStaticMethod('complete nonsense');
            $this->fail();
        } catch (WrongArgumentException $e) {
            /* pass */
        }

        try {
            ClassUtils::callStaticMethod('Identifier::comp::lete::non::sense');
            $this->fail();
        } catch (WrongArgumentException $e) {
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
        $this->assertTrue(ClassUtils::isInstanceOf('ClassUtilsTestClassChild', 'ClassUtilsTestClass'));
        $this->assertFalse(ClassUtils::isInstanceOf('ClassUtilsTestClass', 'ClassUtilsTestClassChild'));
        $this->assertTrue(ClassUtils::isInstanceOf('ClassUtilsTestClassChild', 'ClassUtilsTestInterface'));
        $this->assertTrue(ClassUtils::isInstanceOf('ClassUtilsTestClass', 'ClassUtilsTestInterface'));
        $this->assertTrue(ClassUtils::isInstanceOf('ClassUtilsTestAbstract', 'ClassUtilsTestInterface'));
        $this->assertTrue(ClassUtils::isInstanceOf('ClassUtilsTestAbstract', 'ClassUtilsTestClass'));
        $this->assertFalse(ClassUtils::isInstanceOf('ClassUtilsTestAbstract', 'ClassUtilsTestClassChild'));

        $base = new ClassUtilsTestClass;
        $this->assertTrue(ClassUtils::isInstanceOf($base, $base));

        $this->assertTrue(ClassUtils::isInstanceOf('ClassUtilsTestAbstract', $base));
        $this->assertFalse(ClassUtils::isInstanceOf($base, 'ClassUtilsTestAbstract'));

        $child = new ClassUtilsTestClassChild();

        $this->assertFalse(ClassUtils::isInstanceOf($base, $child));
        $this->assertTrue(ClassUtils::isInstanceOf($child, $base));

        $this->assertFalse(ClassUtils::isInstanceOf($base, 'ClassUtilsTestClassChild'));
        $this->assertTrue(ClassUtils::isInstanceOf($child, 'ClassUtilsTestClass'));
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

interface ClassUtilsTestInterface
{
}

class ClassUtilsTestClass implements ClassUtilsTestInterface
{
    private $object = null;
    private $text = null;


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

class ClassUtilsTestClassChild extends ClassUtilsTestClass
{
}

;

abstract class ClassUtilsTestAbstract extends ClassUtilsTestClass
{
}

;
?>