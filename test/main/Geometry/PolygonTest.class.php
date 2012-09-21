<?php

	final class PolygonTest extends TestCase
	{
		/**
		 * @return array 
		**/
		public static function providerPolygonToString()
		{
			return 
				array(
					array(
						Polygon::create(Point::create(0, 0)),
						'(0, 0)'
					),
					array(
						Polygon::create(Point::create(0, 0))->
							addVertex(
								Point::create(42, 0)
							),
						'(0, 0), (42, 0)'
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
					array(
						'((0, 0), (0, 42), (42, 0))',
						'(0, 0), (0, 42), (42, 0)'
					),
					array(
						'(0, 0), (0, 42), (42, 42), (42, 0)',
						'(0, 0), (0, 42), (42, 42), (42, 0)'
					),
					array(
						'0, 0, 0, 42, 42, 42, 42, 0',
						'(0, 0), (0, 42), (42, 42), (42, 0)'
					),					
				);
		}		
		
		/**
		 * @dataProvider providerPolygonToString
		**/		
		public function testPolygonToString(Polygon $polygon, $expectedStr)
		{
			$this->assertEquals($expectedStr, $polygon->toString());
		}
		
		/**
		 * @dataProvider providerCreationFromString
		**/		
		public function testCreationFromString($polygon, $expectedStr)
		{
			$this->assertEquals(
				$expectedStr,
				Polygon::create($polygon)->toString()
			);
		}
		
		/**
		 * @expectedException WrongArgumentException
		**/		
		public function testInvalidArg()
		{
			Polygon::create('(1, 2, 3)');
		}
		
		/**
		 * @expectedException WrongArgumentException
		**/		
		public function testInvalidPoint()
		{			
			Polygon::create()->
				addVertex(
					Point::create(42)
				);
		}
	
		public function testBoundingBox()
		{	
			$polygon =
				Polygon::create('(0, 0), (0, 42), (42, 0)');
			
			$expected =
				Polygon::create('(0, 0), (0, 42), (42, 42), (42, 0)');
			
			$this->assertTrue(
				$polygon->getBoundingBox()->isEqual($expected)
			);
		}
		
		public function testVertexMethods()
		{
			$polygon =
				Polygon::create('(0, 0), (0, 1), (1, 0)');
			
			$polygon->addVertex(Point::create(1, -1));
			
			$this->assertTrue(
				$polygon->getVertex(0)->isEqual(Point::create(0, 0))
			);
			
			$polygon->setVertex(0, Point::create(-1, 0));
			
			$this->assertTrue(
				$polygon->getVertex(0)->isEqual(Point::create(-1, 0))
			);
			
			$this->assertTrue(
				$polygon->hasVertex(Point::create(1, 0))
			);
			
			$this->assertFalse(
				$polygon->hasVertex(Point::create(42, 666))
			);
		}
	}
?>