<?php
	
	final class DistanceBetweenPointsExpressionTest extends TestCase
	{
		/**
		 * [[point, point, DialectString], ...]
		 *  
		 * @return array 
		**/
		public static function provider()
		{
			return 
				array(
					array(
						'(0, 42)',
						'(42, 0)',
						'\'(0, 42)\'::POINT <-> \'(42, 0)\'::POINT'
					),
					
					array(
						'(0, 0)',
						Point::create(array(42, 42)),
						'\'(0, 0)\'::POINT <-> \'(42, 42)\'::POINT'
					),	

					array(
						Point::create(array(-42, -42)),
						Point::create(array(69, 2012)),
						'\'(-42, -42)\'::POINT <-> \'(69, 2012)\'::POINT'
					)
				);
		}
		
		/**
		 * @dataProvider provider
		**/		
		public function testToDialect($point1, $point2, $expectedStr)
		{
			$this->assertEquals(
				$expectedStr,
				Expression::distanceBetweenPoints($point1, $point2)->
					toDialectString(PostgresDialect::me())
			);
		}
	}
?>