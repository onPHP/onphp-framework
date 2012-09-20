<?php
	
	final class PointContainsExpressionTest extends TestCase
	{
		/**
		 * [[polygon, point, DialectString], ...]
		 *  
		 * @return array 
		 */
		public static function provider()
		{
			return 
				array(
					array(
						'(0, 0), (0, 42)',
						'(42, 42)',
						'\'(0, 0), (0, 42)\'::POLYGON @> \'(42, 42)\'::POINT'
					),
					
					array(
						'(0, 0), (0, 42)',
						Point::create(array(42, 42)),
						'\'(0, 0), (0, 42)\'::POLYGON @> \'(42, 42)\'::POINT'
					),	

					array(
						Polygon::create(
							array(
								array(0, 0),
								array(0, 3),
								array(3, 0)
							)		
						),
						Point::create(array(1, 1)),
						'\'(0, 0), (0, 3), (3, 0)\'::POLYGON '
						.'@> \'(1, 1)\'::POINT'
					)
				);
		}
		
		/**
		 * @dataProvider provider
		**/		
		public function testToDialect($polygon, $point, $expectedStr)
		{
			$this->assertEquals(
				$expectedStr,
				Expression::containsPoint($polygon, $point)->
					toDialectString(PostgresDialect::me())
			);
		}
	}
?>