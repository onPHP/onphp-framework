<?php
	namespace Onphp\Test;

	final class FloatRangeTest extends TestCase
	{
		/**
		 * @dataProvider rangeDataProvider
		**/
		public function testCreation($min, $max, $throwsException)
		{
			if ($throwsException)
				$this->setExpectedException('\Onphp\WrongArgumentException');
			
			$range = \Onphp\FloatRange::create($min, $max);
		} 
		
		public static function rangeDataProvider()
		{
			return array(
				array(
					1, 1, false
				),
				array(
					1, 222222222222222222222222222, false
				),
				array(
					0.1, 1, false
				),
				array(
					0, 1, false
				),
				array(
					2, 1, false
				)
			);
		}
	}
?>