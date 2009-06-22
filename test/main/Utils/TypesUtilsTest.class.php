<?php
	/* $Id$ */

	final class TypesUtilsTest extends TestCase
	{
		/**
		 * @dataProvider integers
		**/
		public function testSignedToUnsigned($signed, $unsigned)
		{
			$this->assertEquals(TypesUtils::signedToUnsigned($signed), $unsigned);
		}

		/**
		 * @dataProvider integers
		**/
		public function testUnsignedToSigned($values)
		{
			$this->assertEquals($signed, TypesUtils::unsignedToSigned($unsigned));
		}
		
		public static function integers()
		{
			return
				array(
					// signed => unsigned
					'-926365496'	=> '3368601800',
					'16843009'		=> '16843009'
				);
		}
	}
?>