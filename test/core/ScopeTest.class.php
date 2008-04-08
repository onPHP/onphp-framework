<?php
	/* $Id$ */
	
	final class ScopeTest extends TestCase
	{
		public function testTransform()
		{
			$array = array(
				'one'	=> 1,
				'inner'	=> array(
					'two'	=> 2,
					'three'	=> 3,
				)
			);
			
			$arrayCopy = array(
				'one'	=> 1,
				'inner'	=> array(
					'two'	=> 2,
					'three'	=> 3,
				)
			);
			
			$scope = Scope::create()->setScope($array);
			
			$newScope = $scope->transform(
				array(
					'one' => 11,
					'inner' => array(
						'two' => '42'
					)
				)
			);
			
			$expect = array(
				'one'	=> 11,
				'inner'	=> array(
					'two'	=> 42,
					'three'	=> 3,
				)
			);
			
			$this->assertEquals($expect, $newScope->getScope());
			
			$this->assertEquals($arrayCopy, $scope->getScope());
		}
		
		public function testMerge()
		{
			$array = array(
				'one'	=> 1,
				'inner'	=> array(
					'two'	=> 2,
					'three'	=> 3,
				)
			);
			
			$scope = Scope::create()->setScope($array);
			
			$scope->merge(
				array(
					'one' => 11,
					'inner' => array(
						'two' => '42'
					)
				)
			);
			
			$array['inner']['three'] = 33;
			
			$expect = array(
				'one'	=> 11,
				'inner'	=> array(
					'two'	=> 42,
					'three'	=> 33,
				)
			);
			
			$this->assertEquals($expect, $scope->getScope());
		}
		
		public function testInner()
		{
			$array = array(
				'one'	=> 1,
				'inner'	=> array(
					'two'	=> 2,
					'three'	=> 3,
				)
			);
			
			$scope = Scope::create()->setScope($array);
			
			$subScope = $scope->getInnerScope('inner');
			
			$subScope->merge(array('two' => 42));
			
			$subScope->setScopeVar('three', 33);
			
			$expect = array(
				'one'	=> 1,
				'inner'	=> array(
					'two'	=> 42,
					'three'	=> 33,
				)
			);
			
			$this->assertEquals($expect, $scope->getScope());
		}
	}
?>