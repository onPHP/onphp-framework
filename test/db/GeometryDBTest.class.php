<?php
	/**
	 * @group ipdb
	**/
	final class GeometryDBTest extends TestCaseDAO
	{
		/**
		 * @var Polygon
		**/
		private $squareLocation;
		
		/**
		 * @var Point 
		**/
		private $squareCapital;
		
		/**
		 * @var Polygon
		**/
		private $triangleLocation;	
		
		/**
		 * @var Point 
		**/
		private $triangleCapital;		
		
		public function setUp()
		{
			parent::setUp();
			
			$this->squareLocation =
				Polygon::create(
					array(
						array(-21, -21),
						array(-21,  21),
						array( 21,  21),
						array( 21, -21)				
					)
				);
			
			$this->squareCapital =
				Point::create(array(0, 0));	

			$this->triangleLocation =
				Polygon::create(
					array(
						array( 5,  5 ),
						array(55,  5 ),
						array( 5,  55)			
					)
				);
			
			$this->triangleCapital =
				Point::create(array(6, 6));
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				DBPool::me()->setDefault($db);

				TestRegion::dao()->
					add(
						TestRegion::create()-> 
						setName('Great Square')->
						setLocation($this->squareLocation)->
						setCapital($this->squareCapital)						
					);

				TestRegion::dao()->
					add(
						TestRegion::create()-> 
						setName('Great Triangle')->
						setLocation($this->triangleLocation)->
						setCapital($this->triangleCapital)		
					);	
			}
		}		
		
		public function testPointProperty()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				DBPool::me()->setDefault($db);			
			
				$triangle =
					Criteria::create(TestRegion::dao())->
					add(
						Expression::eqPoints(
							DBField::create('capital'),
							$this->triangleCapital
						)
					)->
					get();

				$this->assertInstanceOf('Polygon', $triangle->getLocation());	
				$this->assertEquals('Great Triangle', $triangle->getName());
			}
		}
		
		public function testPolygonProperty()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				DBPool::me()->setDefault($db);

				$list =
					Criteria::create(TestRegion::dao())->
					addOrder('id')->
					getList();

				$this->assertEquals(2, count($list));

				$this->assertInstanceOf('Polygon', $list[0]->getLocation());
				$this->assertInstanceOf('Polygon', $list[1]->getLocation());			

				$this->assertTrue(
					$this->squareLocation->
						isEqual($list[0]->getLocation())
				);

				$this->assertTrue(
					$this->triangleLocation->
						isEqual($list[1]->getLocation())
				);

				$square =
					Criteria::create(TestRegion::dao())->
					add(
						Expression::containsPoint(
							DBField::create('location'),
							Point::create(array(1, 1))
						)
					)->
					get();

				$this->assertInstanceOf('Polygon', $square->getLocation());
				$this->assertEquals('Great Square', $square->getName());				
			}
		}
	}
?>