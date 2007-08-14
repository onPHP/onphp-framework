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
/* $Id$ */

	final class MathTest extends UnitTestCase
	{
		public function runMathTest(BigNumberFactory $factory)
		{
			$this->assertEqual(
				'4',
				$factory->
					makeNumber(2)->
					add($factory->makeNumber(2))->
					toString()
			);
			
			$this->assertEqual(
				'281470681743360',
				$factory->
					makeNumber('281474976710656')->
					subtract($factory->makeNumber('4294967296'))->
					toString()
			);
			
			$this->assertEqual(
				'281470681743360',
				$factory->
					makeNumber(2)->
					pow(48)->
					subtract(
						$factory->
							makeNumber(2)->
							pow(32)
					)->
					toString()
			);
			
			$this->assertEqual(
				$factory->makeFromBinary("\x00")->toString(), 
				0
			);
			
			$this->assertEqual(
				$factory->makeFromBinary("\x01")->toString(), 
				1
			);
			
			$this->assertEqual(
				$factory->makeFromBinary("\x7F")->toString(), 
				127
			);
			
			$this->assertEqual(
				$factory->makeFromBinary("\x00\x80")->toString(), 
				128
			);
			
			$this->assertEqual(
				$factory->makeFromBinary("\x00\x81")->toString(),
				129
			);
			
			$this->assertEqual(
				$factory->makeFromBinary("\x00\xFF")->toString(), 
				255
			);
			
			$this->assertEqual(
				$factory->makeFromBinary("\x00\x80\x00")->toString(), 
				32768
			);
		}
		
		/* void */ public function testGmp()
		{
			if (!extension_loaded('gmp')) {
				if (!@dl('gmp.so')) {
					return;
				}
			}
			
			$this->runMathTest(GmpBigIntegerFactory::me());
		}
	}
?>