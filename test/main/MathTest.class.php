<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class MathTest extends TestCase
	{
		public function runMathTest(BigNumberFactory $factory)
		{
			$this->assertEquals(
				'4',
				$factory->
					makeNumber(2)->
					add($factory->makeNumber(2))->
					toString()
			);
			
			$this->assertEquals(
				'281470681743360',
				$factory->
					makeNumber('281474976710656')->
					subtract($factory->makeNumber('4294967296'))->
					toString()
			);
			
			$this->assertEquals(
				'281470681743360',
				$factory->
					makeNumber(2)->
					pow($factory->makeNumber(48))->
					subtract(
						$factory->
							makeNumber(2)->
							pow($factory->makeNumber(32))
					)->
					toString()
			);
			
			$binaryConversions = array(
				"\x00"			=> '0',
				"\x01"			=> '1',
				"\x7F"			=> '127',
				"\x00\x80"		=> '128',
				"\x00\x81"		=> '129',
				"\x00\xFF"		=> '255',
				"\x00\x80\x00"	=> '32768'
			);
			
			foreach ($binaryConversions as $binary => $string) {
				$this->assertEquals(
					$factory->makeFromBinary($binary)->toString(),
					$string
				);
				$this->assertEquals(
					$factory->makeNumber($string)->toBinary(),
					$binary
				);
			}
			
			$this->assertTrue(
				is_float($factory->makeNumber('1')->floatValue())
			);
			$this->assertTrue(
				is_float($factory->makeNumber('12')->floatValue())
			);
			$this->assertTrue(
				is_float($factory->makeNumber('123')->floatValue())
			);
			$this->assertTrue(
				is_float($factory->makeNumber('1234')->floatValue())
			);
			$this->assertTrue(
				is_float($factory->makeNumber('12345')->floatValue())
			);
			$this->assertTrue(
				is_float($factory->makeNumber('123456')->floatValue())
			);
			$this->assertTrue(
				is_float($factory->makeNumber('1234567')->floatValue())
			);
		}
		
		public function runRandomTest(BigNumberFactory $factory, RandomSource $source)
		{
			$this->assertNotEquals(
				$factory->
					makeRandom(100, $source)->
					cmp($factory->makeRandom(100, $source)),
				0
			);
			
			$this->assertNotEquals(
				$factory->
					makeRandom('123456789012345678901234567890', $source)->
					cmp(
						$factory->makeRandom(
							'123456789012345678901234567890',
							$source
						)
					),
				0
			);
		}
		
		/* void */ public function testGmp()
		{
			if (!extension_loaded('gmp')) {
				try {
					dl('gmp.so');
				} catch (BaseException $e) {
					return $this->markTestSkipped('gmp module not available');
				}
			}
			
			$this->runMathTest(GmpBigIntegerFactory::me());
		}
		
		public function runRandomSourceTest(RandomSource $source)
		{
			$this->assertNotEquals($source->getBytes(2), $source->getBytes(2));
			$this->assertNotEquals($source->getBytes(10), $source->getBytes(10));
			$this->assertNotEquals($source->getBytes(256), $source->getBytes(256));
		}
		
		public function testRandomSource()
		{
			$this->runRandomSourceTest(MtRandomSource::me());
			
			if (file_exists('/dev/urandom') && is_readable('/dev/urandom'))
				$this->runRandomSourceTest(new FileRandomSource('/dev/urandom'));
		}
	}
?>