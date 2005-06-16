<?php
/*
* :tabSize=4:indentSize=4:noTabs=true:
* :folding=explicit:collapseFolds=1:
*/

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.inc.php';
require_once 'PHPUnit.php';

//{{{ PropertyFilter_Test
class PropertyFilter_Test extends PHPUnit_TestCase {
    
    //{{{ properties
    private $setting;
    //}}}
    
    //{{{ setUP()
    function setUP() {
        $this->setting = PropertyFilter::getInstance('foo', 'bar', 'zoo');
        $this->setting->var1 = array(3 => 1, 4, 5);
        $this->setting->var2 = '<var2>';
        $this->setting->setDefault('test');
        try {
            $this->setting->createFunction('test_func', '$summands',
            '$result = 0; foreach ($summands as $summand) {$result += $summand;} return $result;');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }
    //}}}
    
    //{{{ test_issetDefault
    function test_issetDefault() {
        //$this->assertFalse($this->setting->issetDefault());
        $this->assertTrue($this->setting->issetDefault());
    }
    //}}}
    
    //{{{ test_getDefault
    function test_getDefault() {
        $this->assertEquals('test', $this->setting->getDefault());
    }
    //}}}
    
    //{{{ test___get
    function test___get() {
        $this->setting->setDefaultHandler('htmlspecialchars');
        $this->assertEquals(array(3 => 1, 4, 5), $this->setting->var1);
        $this->assertEquals('&lt;var2&gt;', $this->setting->var2);
        $this->assertEquals('test', $this->setting->var3);
        $this->setting->var4 = '<var"4>';
        $this->setting->var5 = "<var'5>";
        $this->assertEquals('&lt;var&quot;4&gt;', $this->setting->var4);
        $this->assertEquals("&lt;var'5&gt;", $this->setting->var5);
        $this->setting->setDefaultHandler('htmlspecialchars', array(ENT_QUOTES));
        $this->assertEquals('&lt;var&quot;4&gt;', $this->setting->var4);
        $this->assertEquals('&lt;var&#039;5&gt;', $this->setting->var5);
        $this->setting->var6 = array('<', 1, '>');
        $this->assertEquals(array('&lt;', 1, '&gt;'), $this->setting->var6);
    }
    //}}}
    
    //{{{ test_create_function
    function test_create_function() {
        $this->assertTrue($this->setting->createFunction('test2_func', '$summands',
                                        '$result = 0; foreach ($summands as $summand) $result += $summand; return $result;'));
        try {
            $this->setting->createFunction('test_func', '$summands',
                                        '$result = 0; foreach ($summands as $summand) $result += $summand; return $result;');
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }
    //}}}
    
    //{{{ test___call
    function test___call() {
        $this->assertEquals('test', $this->setting->unknownFunction());
        $this->assertEquals(6, $this->setting->test_func(1, 2, 3));
    }
    //}}}
    
    //{{{ test_getDefaultHandler
    function test_getDefaultHandler() {
        $this->assertEquals('foo', $this->setting->getDefaultHandler());
    }
    //}}}
    
    //{{{ test_getDefaultParams
    function test_getDefaultParams() {
        $this->assertEquals(array('bar', 'zoo'), $this->setting->getDefaultParams());
    }
    //}}}
    
    //{{{ test_factory
    function test_factory() {
        $setting = PropertyFilter::getInstance();
        $setting->var4 = '<var"4>';
        $setting->var5 = "<var'5>";
        $setting->var6 = array('<', 1, '>');
        $this->assertEquals('<var"4>', $setting->var4);
        $this->assertEquals("<var'5>", $setting->var5);
        $this->assertEquals(array('<', 1, '>'), $setting->var6);
    }
    //}}}
    
    //{{{ test_getTemplates
    function test_getTemplates() {
        $this->assertEquals(array(), $this->setting->getTemplates());
        $this->setting->setTemplates('foo', 'bar', 'zoo');
        $this->assertEquals(array('foo', 'bar', 'zoo'), $this->setting->getTemplates());
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

$suite = new PHPUnit_TestSuite('PropertyFilter_Test'); 
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
