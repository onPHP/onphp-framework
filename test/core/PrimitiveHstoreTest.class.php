<?php
	/* $Id$ */
	
	final class PrimitiveHstoreTest extends TestCase
	{
		protected static $scope = 
			array(
				'properties' => array(
					 'a' => '1',
					 'b' => '2',
					 'c' => '3',
				)
			);
		
		public function testImport()
		{
			$prm = $this->create();
			$this->assertTrue(
				$prm->import(self::$scope)
			);
			$this->assertFalse($prm->isCheckAllowedKeys());
			$prm->clean();
		}
		
		public function testMax() 
		{
			$prm = $this->create();
			$this->assertFalse(
				$prm->
					setMax(2)->
					import(self::$scope)
			);
			$this->assertFalse($prm->isCheckAllowedKeys());
			$prm->clean();
		}
		
		public function testMin() 
		{	
			$prm = $this->create();
			$this->assertFalse(
				$prm->
					setMin(4)->
					import(self::$scope)
			);
			$this->assertFalse($prm->isCheckAllowedKeys());
			$prm->clean();
		}	
		
		public function testValidKeys() 
		{
			$prm = $this->create();
			$this->assertTrue(
				$prm->
					setAllowedKeys(
						array('a', 'b', 'c', 'd', 'e')
					)->
					import(self::$scope)
			);
			
			$this->assertTrue($prm->isCheckAllowedKeys());
			$prm->clean();
		}
		
		public function testInvalidKeys() 
		{	
			$prm = $this->create();
					
			$this->assertFalse(
				$prm->
					setAllowedKeys(
						array('a', 'b', 'd')
					)->
					import(self::$scope)
			);
			
			$this->assertTrue($prm->isCheckAllowedKeys());			
			$prm->clean();			
		}
		
		protected function create() 
		{
			return Primitive::hstore('properties');
		}
	}
?>