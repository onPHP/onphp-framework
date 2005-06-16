<?php
/*
* :tabSize=4:indentSize=4:noTabs=true:
* :folding=explicit:collapseFolds=1:
*/

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.inc.php';
require_once 'PHPUnit.php';

//{{{ Dummy5_Test
class Dummy5_Test extends PHPUnit_TestCase {
    
    //{{{ properties
    private $dummy;
    //}}}
    
    //{{{ setUP()
    function setUP() {
        $this->dummy = new Dummy;
        $this->dummy->var1 = array(3 => 1, 4, 5);
        $this->dummy->var2 = 'var2';
        $this->dummy->setDefault('test');
        $this->dummy->createFunction('test_func', '$summands',
        '$result = 0; foreach ($summands as $summand) {$result += $summand;} return $result;');
    }
    //}}}
    
    //{{{ test_issetDefault
    function test_issetDefault() {
        //$this->assertFalse($this->dummy->issetDefault());
        $this->assertTrue($this->dummy->issetDefault());
    }
    //}}}
    
    //{{{ test_getDefault
    function test_getDefault() {
        $this->assertEquals('test', $this->dummy->getDefault());
    }
    //}}}
    
    //{{{ test___get
    function test___get() {
        $this->assertEquals(array(3 => 1, 4, 5), $this->dummy->var1);
        $this->assertEquals('var2', $this->dummy->var2);
        $this->assertEquals('test', $this->dummy->var3);
    }
    //}}}
    
    //{{{ test_create_function
    function test_createFunction() {
        $this->assertTrue($this->dummy->createFunction('test2_func', '$summands',
                                        'var_dump($summands);$result = 0; foreach ($summands as $summand) $result += $summand; return $result;'));
        //$this->assertEquals(3, $this->dummy->test2_func(array(1,2)));
        try {
            $this->dummy->createFunction('test_func', '$summands',
                                        '$result = 0; foreach ($summands as $summand) $result += $summand; return $result;');
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }
    //}}}
    
    //{{{ test___call
    function test___call() {
        $this->assertEquals('test', $this->dummy->unknownFunction());
        $this->assertEquals(6, $this->dummy->test_func(1, 2, 3));
    }
    //}}}
    
    /*
    //{{{ test_
    function test_() {
        $this->assertFalse();
        $this->assertEquals();
    }
    //}}}
    */
    
}
//}}}

$suite = new PHPUnit_TestSuite('Dummy5_Test'); 
$result = PHPUnit::run($suite); 
if (isset($_SERVER['HTTP_ACCEPT'])) {
    if (!$result->wasSuccessful()) {
        echo '<h1>Attention! Some tests failed!</h1>';
    }
    echo $result->toHtml();
} else {
    echo $result->toString();
    if (!$result->wasSuccessful()) {
        echo 'Attention! Some tests failed!';
    }
}

/* Copyright 2004 Sveta Smirnova & Sergey Lasunov */
?>
