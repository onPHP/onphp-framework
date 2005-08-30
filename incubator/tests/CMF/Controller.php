<?php
/*$Id$*/

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'CMF/Controller.class.php';

class TestedController extends Controller
{
	public function getCurrentAction()
	{
		return parent::getCurrentAction();
	}
	
	public function moduleHandler()
	{
		return parent::moduleHandler();
	}
	
	public function templateHandler()
	{
		return parent::templateHandler();
	}
	
}

class ControllerTest extends UnitTestCase
{
	private $controller;
	
	public function __construct()
	{
		$this->UnitTestCase('ControllerTest');
	}
	
	public function setUp()
	{
		$this->controller = new TestedController;
		$this->controller->setModuleDir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'modules')->
							setTemplateDir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates');
		$_GET = array();
	}
	
	public function testGlobals()
	{
		$this->assertEqual(array('_GET', '_POST', '_SESSION', '_COOKIE'), $this->controller->getGlobals());
		$this->controller->useGlobal('_GET', 2);
		$this->assertEqual(array('_POST', '_SESSION', '_GET', '_COOKIE'), $this->controller->getGlobals());
		$this->controller->useGlobal('custom');
		$this->assertEqual(array('_POST', '_SESSION', '_GET', '_COOKIE', 'custom'), $this->controller->getGlobals());
		$this->controller->useGlobal('other', 1);
		$this->assertEqual(array('_POST', 'other', '_SESSION', '_GET', '_COOKIE', 'custom'), $this->controller->getGlobals());
		$this->controller->dontUseGlobal('_POST');
		$this->assertEqual(array('other', '_SESSION', '_GET', '_COOKIE', 'custom'), $this->controller->getGlobals());
		
	}
	
	public function testSetters()
	{
		$this->assertEqual(array($this->controller, 'moduleHandler'), $this->controller->getModuleHandler());
		$this->assertEqual($this->controller, $this->controller->setModuleHandler('custom'));
		$this->assertEqual('custom', $this->controller->getModuleHandler());
		$this->assertEqual($this->controller, $this->controller->setModuleHandler(array($this->controller, 'moduleHandler')));
		$this->assertEqual(array($this->controller, 'moduleHandler'), $this->controller->getModuleHandler());
		
		$this->assertEqual(array($this->controller, 'templateHandler'), $this->controller->getTemplateHandler());
		$this->assertEqual($this->controller, $this->controller->setTemplateHandler('custom'));
		$this->assertEqual('custom', $this->controller->getTemplateHandler());
		$this->assertEqual($this->controller, $this->controller->setTemplateHandler(array($this->controller, 'templateHandler')));
		$this->assertEqual(array($this->controller, 'templateHandler'), $this->controller->getTemplateHandler());
		
		$this->assertEqual($this->controller, $this->controller->setModuleDir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'modules'));
		$this->assertEqual(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'modules', $this->controller->getModuleDir());
		
		$this->assertEqual($this->controller, $this->controller->setTemplateDir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates'));
		$this->assertEqual(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates', $this->controller->getTemplateDir());
		
		$this->assertEqual('.inc.php', $this->controller->getModuleExt());
		$this->assertEqual($this->controller, $this->controller->setModuleExt('custom'));
		$this->assertEqual('custom', $this->controller->getModuleExt());
		$this->assertEqual($this->controller, $this->controller->setModuleExt('.inc.php'));
		$this->assertEqual('.inc.php', $this->controller->getModuleExt());
		
		$this->assertEqual('.tpl.html', $this->controller->getTemplateExt());
		$this->assertEqual($this->controller, $this->controller->setTemplateExt('custom'));
		$this->assertEqual('custom', $this->controller->getTemplateExt());
		$this->assertEqual($this->controller, $this->controller->setTemplateExt('.tpl.html'));
		$this->assertEqual('.tpl.html', $this->controller->getTemplateExt());
		
		$this->assertEqual('action', $this->controller->getAction());
		$this->assertEqual($this->controller, $this->controller->setAction('custom'));
		$this->assertEqual('custom', $this->controller->getAction());
		$this->assertEqual($this->controller, $this->controller->setAction('action'));
		$this->assertEqual('action', $this->controller->getAction());
		
		$this->assertEqual('default', $this->controller->getDefault());
		$this->assertEqual($this->controller, $this->controller->setDefault('custom'));
		$this->assertEqual('custom', $this->controller->getDefault());
		$this->assertEqual($this->controller, $this->controller->setDefault('default'));
		$this->assertEqual('default', $this->controller->getDefault());
	}
	
	public function testGetCurrentAction()
	{
		$this->assertEqual(array(), $this->controller->getCurrentAction());
		$_GET['action'] = 'second';
		$this->assertEqual(array('second'), $this->controller->getCurrentAction());
		$this->assertNotEqual(array('invalid'), $this->controller->getCurrentAction());
		$_GET['second'] = 'third';
		$this->assertEqual(array('second', 'third'), $this->controller->getCurrentAction());
		$_GET['third'] = 'fourth';
		$this->assertEqual(array('second', 'third', 'fourth'), $this->controller->getCurrentAction());
	}
	
	public function testModuleHandler()
	{
		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'first.inc.php';
		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'second.inc.php';
		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR .
					'third' . DIRECTORY_SEPARATOR . 'tfirst.inc.php';
		$this->assertEqual($this->controller, $this->controller->moduleHandler());
		$this->assertEqual(new first, $this->controller->getModule());
		$_GET['action'] = 'third';
		$this->assertEqual($this->controller, $this->controller->moduleHandler());
		$this->assertEqual(new tfirst, $this->controller->getModule());
		$_GET['action'] = 'second';
		$this->assertEqual($this->controller, $this->controller->moduleHandler());
		$this->assertEqual(new second, $this->controller->getModule());
		$_GET['second'] = 'invalid';
		$this->assertEqual($this->controller, $this->controller->moduleHandler());
		$this->assertEqual(new second, $this->controller->getModule());
		$_GET['action'] = 'third';
		$_GET['third'] = 'invalid';
		$this->assertEqual($this->controller, $this->controller->moduleHandler());
		$this->assertEqual(new tfirst, $this->controller->getModule());
		$_GET['action'] = 'invalid';
		$_GET['invalid'] = 'bad';
		$this->assertEqual($this->controller, $this->controller->moduleHandler());
		$this->assertEqual(new first, $this->controller->getModule());
	}
	
	public function testTemplateHandler()
	{
		$this->assertEqual($this->controller, $this->controller->templateHandler());
		$this->assertEqual('first', $this->controller->getTemplate());
		$_GET['action'] = 'third';
		$this->assertEqual($this->controller, $this->controller->templateHandler());
		$this->assertEqual('third\tfirst', $this->controller->getTemplate());
		$_GET['action'] = 'second';
		$this->assertEqual($this->controller, $this->controller->templateHandler());
		$this->assertEqual('second', $this->controller->getTemplate());
		$_GET['second'] = 'invalid';
		$this->assertEqual($this->controller, $this->controller->templateHandler());
		$this->assertEqual('second', $this->controller->getTemplate());
		$_GET['action'] = 'third';
		$_GET['third'] = 'invalid';
		$this->assertEqual($this->controller, $this->controller->templateHandler());
		$this->assertEqual('third\tfirst', $this->controller->getTemplate());
		$_GET['action'] = 'invalid';
		$_GET['invalid'] = 'bad';
		$this->assertEqual($this->controller, $this->controller->templateHandler());
		$this->assertEqual('first', $this->controller->getTemplate());
	}
	
}

$test = new ControllerTest();
exit ($test->run(new TextReporter()) ? 0 : 1);

?>
