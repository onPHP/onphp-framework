<?php
	namespace Onphp\Test;

	final class TypesUtilsTest extends TestCase
	{
		/**
		 * @dataProvider integers
		**/
		public function testSignedToUnsigned($signed, $unsigned)
		{
			$this->assertEquals(\Onphp\TypesUtils::signedToUnsigned($signed), $unsigned);
		}

		/**
		 * @dataProvider integers
		**/
		public function testUnsignedToSigned($signed, $unsigned)
		{
			$this->assertEquals($signed, \Onphp\TypesUtils::unsignedToSigned($unsigned));
		}
		
		public static function integers()
		{
			return
				array(
					// signed, unsigned
					array('-926365496', '3368601800'),
					array('16843009', '16843009')
				);
		}
	}
?>