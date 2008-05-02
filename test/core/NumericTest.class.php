<?php
	/* $Id$ */
	
	final class NumericTest extends TestCase
	{
		public static function numericProvider()
		{
			// value and validity for: positive, negative, any integer and float
			
			return array(
				array(0, false, false, true, true),
				array(1, true, false, true, true),
				array(-1, false, true, true, true),
				array('42', true, false, true, true),
				array(-28, false, true, true, true),
				array('-11', false, true, true, true),
				array('string', false, false, false, false),
				array(new stdClass(), false, false, false, false),
				array(28.42, false, false, false, true),
				array(-48.28, false, false, false, true),
				array('1e2+3', false, false, false, false)
			);
		}
		
		/**
		 * @dataProvider numericProvider
		**/
		public function testNumeric($value, $positive, $negative, $any, $float)
		{
			foreach (
				array(
					'PositiveInteger' => 'positive',
					'NegativeInteger' => 'negative',
					'Integer' => 'any',
					'Float' => 'float'
				)
				as $className => $type
			) {
				try {
					$object = new $className($value);
					
					$this->assertEquals($object->getValue(), $value);
					
					if (!$$type)
						$this->fail($value.' can not be '.$type.' number');
				} catch (WrongArgumentException $e) {
					if ($$type)
						$this->fail($e->getMessage());
				} catch (OutOfRangeException $e) {
					if ($$type)
						$this->fail($e->getMessage());
				}
			}
		}
		
		public function testLimitSetters()
		{
			$int = new PositiveInteger();
			
			try {
				$int->setMin(5)->setMax(0);
				
				$this->fail();
			} catch (WrongArgumentException $e) {/*_*/}
			
			try {
				$int->setMax(10)->setMin(11);
				
				$this->fail();
			} catch (WrongArgumentException $e) {/*_*/}
		}
	}
?>