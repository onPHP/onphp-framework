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

	final class CryptoTest extends UnitTestCase
	{
		public function runDiffieHellmanExchange(BigNumberFactory $factory)
		{
			$parameters = DiffieHellmanParameters::create(
				$factory->makeNumber(2),
				$factory->makeNumber(
					'155172898181473697471232257763715539915724801'.
			        '966915404479707795314057629378541917580651227423'.
			        '698188993727816152646631438561595825688188889951'.
			        '272158842675419950341258706556549803580104870537'.
			        '681476726513255747040765857479291291572334510643'.
			        '245094715007229621094194349783925984760375594985'.
			        '848253359305585439638443'
				)
			);
			
			$sideA = DiffieHellmanKeyPair::generate(
				$parameters, 
				MtRandomSource::me()
			);
			
			$sideB = DiffieHellmanKeyPair::generate(
				$parameters, 
				MtRandomSource::me()
			);
			
			$this->assertEqual(
				$sideA->makeSharedKey($sideB->getPublic())->toString(),
				$sideB->makeSharedKey($sideA->getPublic())->toString()
			);
		}
		
		/* void */ public function testGmp()
		{
			if (!extension_loaded('gmp')) {
				if (!@dl('gmp.so')) {
					return;
				}
			}
			
			$this->runDiffieHellmanExchange(GmpBigIntegerFactory::me());
		}
	}
?>