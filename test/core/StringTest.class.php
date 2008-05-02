<?php
	/* $Id$ */
	
	final class StringTest extends TestCase
	{
		public static function stringProvider()
		{
			return array(
				array(0, false),
				array(1, false),
				array(-1, false),
				array('42', true),
				array(-28, false),
				array('-11', true),
				array('string', true),
				array(new stdClass(), false),
				array(28.42, false),
				array(-48.28, false),
				array('1e2+3', true)
			);
		}
		
		/**
		 * @dataProvider stringProvider
		**/
		public function testString($value, $string)
		{
			try {
				$object = new String($value);
				
				$this->assertEquals($object->get(), $value);
				
				if (!$string)
					$this->fail(
						Assert::dumpArgument($string).' is not a string'
					);
			} catch (WrongArgumentException $e) {
				if ($string)
					$this->fail($e->getMessage());
			}
		}
	}
?>