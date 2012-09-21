<?php

	final class PointTest extends TestCase
	{
		/**
		 * @return array 
		**/
		public static function providerPointToString()
		{
			return 
				array(
					array(
						Point::create(
							array(0, 42)
						),
						'(0, 42)'
					),
					array(
						Point::create(
							array(-1)
						),
						'(-1)'
					)					
				);
		}
		
		/**
		 * @return array 
		**/
		public static function providerCreationFromString()
		{
			return 
				array(
					array('0, 42', '(0, 42)'),
					array('(0,42)', '(0, 42)'),					
					array('-1', '(-1)'),
					array('1, 2, 3', '(1, 2, 3)'),					
				);
		}
		
		public function testFactory()
		{
			$point = Point::create(42, 24);

			$this->assertTrue(
				$point->isEqual(Point::create('(42, 24)'))
			);
			
			$this->assertTrue(
				$point->isEqual(Point::create('42, 24'))
			);
			
			$this->assertTrue(
				$point->isEqual(Point::create(array(42, 24)))
			);
		}		
		
		/**
		 * @dataProvider providerPointToString
		**/		
		public function testPointToString(Point $point, $expectedStr)
		{
			$this->assertEquals($expectedStr, $point->toString());
		}
		
		/**
		 * @dataProvider providerCreationFromString
		**/		
		public function testCreationFromString($point, $expectedStr)
		{
			$this->assertEquals(
				$expectedStr,
				Point::create($point)->toString()
			);
		}
				
		public function testCoordinates()
		{
			$this->assertEquals(
				3, 
				Point::create(array(1, 2, 42))->
					getNumberOfCoordinates()
			);			
			
			$this->assertTrue(
				Point::create(array(19, 91))->
					belongsToPlane()
			);
			
			$this->assertFalse(
				Point::create(array(42))->
					belongsToPlane()
			);			
		}
		
		public function testGettersSetters()
		{
			$point = Point::create(array(1, 1, 42));
			
			$this->assertEquals(42, $point->getZ());
			
			$this->assertEquals(8, $point->setY(8)->getY());
		}
	}
?>