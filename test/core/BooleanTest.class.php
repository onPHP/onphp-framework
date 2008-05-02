<?php
	/* $Id$ */

	final class BooleanTest extends TestCase
	{
		public static function booleanProvider()
		{
			return array(
				array(true, true),
				array(false, false),
				array(null, false),
				array(1, true),
				array(0, false),
				array(-1, true),
				array('123', true),
				array(new stdClass(), true),
				array(28.42, true),
				array(array(), false),
				array(array(0), true)
			);
		}
		
		/**
		 * @dataProvider booleanProvider
		**/
		public function testBoolean($value, $result)
		{
			$this->assertEquals(
				Boolean::create($value)->getValue(),
				$result
			);
		}
	}
?>