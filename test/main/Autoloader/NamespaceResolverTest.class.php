<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexey S. Denisov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @group ns
	 */
	namespace Onphp\Test;

	final class NamespaceResolverTest extends TestCase
	{
		public function testOnPHPResolver()
		{
			//setup
			$resolver = \Onphp\NamespaceResolverOnPHP::create()->
				addPath($this->getBasePath().'onPHP');
			//expectation
			$result = array(
				0 => $this->getBasePath().'onPHP/',
				'\MyForm' => 0,
			);
			//execution
			$this->assertEquals($result, $resolver->getClassPathList());
			$this->assertEquals($this->getBasePath().'onPHP/MyForm.class.php', $resolver->getClassPath('MyForm'));
			$this->assertNull($resolver->getClassPath('MyFormSub'));
			$this->assertNull($resolver->getClassPath('\onPHP\MyFormSub'));
			$this->assertNull($resolver->getClassPath('\onPHP\MyFormSup'));
			
			//setup
			$resolver->addPaths(
				array($this->getBasePath().'onPHP/Sub/', $this->getBasePath().'onPHP/Up/'),
				'onPHP'
			);
			//expectation
			$result[1] = $this->getBasePath().'onPHP/Sub/';
			$result['\onPHP\MyFormSub'] = 1;
			$result[2] = $this->getBasePath().'onPHP/Up/';
			$result['\onPHP\MyFormUp'] = 2;
			//execution
			$this->assertEquals($result, $resolver->getClassPathList());
			$this->assertEquals($this->getBasePath().'onPHP/MyForm.class.php', $resolver->getClassPath('\MyForm'));
			$this->assertNull($resolver->getClassPath('MyFormSub'));
			$this->assertEquals(
				$this->getBasePath().'onPHP/Sub/MyFormSub.class.php',
				$resolver->getClassPath('\onPHP\MyFormSub')
			);
			$this->assertNull($resolver->getClassPath('\onPHP\MyFormSup'));
			
			//setup
			$resolver->setClassExtension('.clazz.php');
			//expecation
			$result = array(
				0 => $this->getBasePath().'onPHP/',
				1 => $this->getBasePath().'onPHP/Sub/',
				'\\onPHP\\MyFormSup' => 1,
				2 => $this->getBasePath().'onPHP/Up/',
			);
			//execution
			$this->assertEquals($result, $resolver->getClassPathList());
			$this->assertNull($resolver->getClassPath('\MyForm'));
			$this->assertNull($resolver->getClassPath('MyFormSub'));
			$this->assertNull($resolver->getClassPath('\onPHP\MyFormSub'));
			$this->assertEquals(
				$this->getBasePath().'onPHP/Sub/MyFormSup.clazz.php',
				$resolver->getClassPath('\onPHP\MyFormSup')
			);
		}
		
		public function testPSR0ResolverEmptyBaseNamespace()
		{
			//setup
			$resolver = \Onphp\NamespaceResolverPSR0::create()->
				addPath($this->getBasePath());
			
			//expectation
			$result = array(
				0 => $this->getBasePath(),
				1 => $this->getBasePath().'MyNS/',
				'\MyNS\\Class1' => 1,
				2 => $this->getBasePath().'MyNS/Sub/',
				'\\MyNS\Sub\Class2' => 2,
				3 => $this->getBasePath().'onPHP/',
				4 => $this->getBasePath().'onPHP/Sub/',
				5 => $this->getBasePath().'onPHP/Up/',
				'\onPHP\MyForm' => 3,
				'\onPHP\Sub\MyFormSub' => 4,
				'\onPHP\Up\MyFormUp' => 5,
			);
			//execution
			$this->assertEquals($result, $resolver->getClassPathList());
			$this->assertEquals(
				$this->getBasePath().'MyNS/Sub/Class2.class.php',
				$resolver->getClassPath('\MyNS\\Sub\Class2')
			);
			$this->assertNull($resolver->getClassPath('\MyNS\\Sub\Class1'));
			$this->assertNull($resolver->getClassPath('\onPHP\Sub\MyFormSup'));
			
			//setup
			$resolver->setClassExtension('.clazz.php');
			//expectation
			$result = array(
				0 => $this->getBasePath(),
				1 => $this->getBasePath().'MyNS/',
				2 => $this->getBasePath().'MyNS/Sub/',
				3 => $this->getBasePath().'onPHP/',
				4 => $this->getBasePath().'onPHP/Sub/',
				'\onPHP\Sub\MyFormSup' => 4,
				5 => $this->getBasePath().'onPHP/Up/',
			);
			//execution
			$this->assertEquals($result, $resolver->getClassPathList());
			$this->assertNull($resolver->getClassPath('\MyNS\Sub\Class2'));
			$this->assertEquals(
				$this->getBasePath().'onPHP/Sub/MyFormSup.clazz.php',
				$resolver->getClassPath('\onPHP\Sub\MyFormSup')
			);
		}
		
		public function testPSR0ResolverStartWithNamespace()
		{
			//setup
			$resolver = \Onphp\NamespaceResolverPSR0::create()->
				addPath($this->getBasePath().'onPHP', '\onPHP\\');
			//expectation
			$result = array(
				0 => $this->getBasePath().'onPHP/',
				'\onPHP\MyForm' => 0,
				1 => $this->getBasePath().'onPHP/Sub/',
				'\onPHP\Sub\MyFormSub' => 1,
				2 => $this->getBasePath().'onPHP/Up/',
				'\onPHP\Up\MyFormUp' => 2,
			);
			//execution
			$this->assertEquals($result, $resolver->getClassPathList());
			$this->assertEquals(
				$this->getBasePath().'onPHP/Up/MyFormUp.class.php',
				$resolver->getClassPath('onPHP\Up\MyFormUp')
			);
			$this->assertNull($resolver->getClassPath('\MyNS\\Sub\Class1'));
			$this->assertNull($resolver->getClassPath('\onPHP\Sub\MyFormSup'));
		}
		
		public function testPSR0WithUnderlineNoBaseNamespace()
		{
			//setup
			$resolver = \Onphp\NamespaceResolverPSR0::create()->
				setAllowedUnderline(true)->
				addPath($this->getBasePath(true));
			
			//expectation
			$this->assertEquals(
				$this->getBasePath(true).'Under/Class.class.php',
				$resolver->getClassPath('\Under_Class')
			);
			$this->assertEquals(
				$this->getBasePath(true).'Under/Class.class.php',
				$resolver->getClassPath('\Under\Class')
			);
			
			$result = array(
				0 => $this->getBasePath(true),
				1 => $this->getBasePath(true).'Under/',
				'\Under\Class' => 1,
				'\Under_Class' => 1,
			);
			$this->assertEquals($result, $resolver->getClassPathList());
			
			//re-setup
			$resolver->setAllowedUnderline(false);
			$this->assertNull($resolver->getClassPath('\Under_Class'));
			$this->assertEquals(
				$this->getBasePath(true).'Under/Class.class.php',
				$resolver->getClassPath('\Under\Class')
			);
		}
		
		public function testPSR0WithUnderlineWithBaseNamespace()
		{
			//setup
			$resolver = \Onphp\NamespaceResolverPSR0::create()->
				setAllowedUnderline(true)->
				addPath($this->getBasePath(true), 'My\base_package');
			
			//expectation
			$this->assertEquals(
				$this->getBasePath(true).'Under/Class.class.php',
				$resolver->getClassPath('\My\base_package\Under_Class')
			);
			$this->assertEquals(
				$this->getBasePath(true).'Under/Class.class.php',
				$resolver->getClassPath('\My\base_package\Under\Class')
			);
			$this->assertNull($resolver->getClassPath('\Under\Class'));
			
			$result = array(
				0 => $this->getBasePath(true),
				1 => $this->getBasePath(true).'Under/',
				'\My\base_package\Under\Class' => 1,
				'\My\base_package\Under_Class' => 1,
			);
			$this->assertEquals($result, $resolver->getClassPathList());
			
			//re-setup
			$resolver->setAllowedUnderline(false);
			$this->assertNull($resolver->getClassPath('\My\base_package\Under_Class'));
			$this->assertEquals(
				$this->getBasePath(true).'Under/Class.class.php',
				$resolver->getClassPath('\My\base_package\Under\Class')
			);
		}
		
		private function getBasePath($isUnderline = false)
		{
			return ONPHP_TEST_PATH.(
				$isUnderline
					? 'main/data/namespace_ul/'
					: 'main/data/namespace/'
			);
		}
	}
?>