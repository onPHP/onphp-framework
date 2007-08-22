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
		public function runDiffieHellmanExchange(
			BigNumberFactory $factory,
			RandomSource $source
		)
		{
			$parameters = DiffieHellmanParameters::create(
				$factory->makeNumber(2),
				$factory->makeNumber(
					'155172898181473697471232257763715539915724801'
			        .'966915404479707795314057629378541917580651227423'
			        .'698188993727816152646631438561595825688188889951'
			        .'272158842675419950341258706556549803580104870537'
			        .'681476726513255747040765857479291291572334510643'
			        .'245094715007229621094194349783925984760375594985'
			        .'848253359305585439638443'
				)
			);
			
			$sideA = DiffieHellmanKeyPair::generate($parameters, $source);
			$sideB = DiffieHellmanKeyPair::generate($parameters, $source);
			
			$this->assertEqual(
				$sideA->makeSharedKey($sideB->getPublic())->toString(),
				$sideB->makeSharedKey($sideA->getPublic())->toString()
			);
		}
		
		public function runDiffieHellmanGeneration(BigNumberFactory $factory)
		{
			$parameters = DiffieHellmanParameters::create(
				$factory->makeNumber(2),
				$factory->makeNumber(126)
			);
			
			$sourceA = new RandomSourceStub("\x02");
			$pairA = DiffieHellmanKeyPair::generate($parameters, $sourceA);
			$this->assertEqual(
				$pairA->getPublic()->toString(),
				'4'
			);
			$this->assertEqual(
				$pairA->getPrivate()->toString(),
				'2'
			);
			
			
			$sourceB = new RandomSourceStub("\x03");
			$pairB = DiffieHellmanKeyPair::generate($parameters, $sourceB);
			$this->assertEqual(
				$pairB->getPublic()->toString(),
				'8'
			);
			$this->assertEqual(
				$pairB->getPrivate()->toString(),
				'3'
			);
			
			$this->assertEqual(
				$pairA->makeSharedKey($pairB->getPublic())->toString(),
				'64'
			);
			
			$this->assertEqual(
				$pairB->makeSharedKey($pairA->getPublic())->toString(),
				'64'
			);
		}
		
		/* void */ public function testGmp()
		{
			if (!extension_loaded('gmp')) {
				if (!@dl('gmp.so')) {
					return;
				}
			}
			
			$this->runDiffieHellmanGeneration(GmpBigIntegerFactory::me());
			
			$this->runDiffieHellmanExchange(
				GmpBigIntegerFactory::me(), 
				MtRandomSource::me()
			);
			
			if (file_exists('/dev/urandom') && is_readable('/dev/urandom'))
				$this->runDiffieHellmanExchange(
					GmpBigIntegerFactory::me(), 
					new FileRandomSource('/dev/urandom')
				);
		}
		
		/**
		 * @see http://csrc.nist.gov/ipsec/papers/rfc2202-testcases.txt
		 */
		public function testHmacsha1()
		{
			$this->assertEqual(
				TextUtils::hex2Binary('b617318655057264e28bc0b6fb378c8ef146be00'),
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b'), 
					"Hi There"
				)
			);
			
			$this->assertEqual(
				CryptoFunctions::hmacsha1(
					"Jefe", 
					"what do ya want for nothing?"
				),
				TextUtils::hex2Binary('effcdf6ae5eb2fa2d27416d5f184df9c259a7c79')
			);
			
			$this->assertEqual(
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'), 
					TextUtils::hex2Binary('dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd')
				),
				TextUtils::hex2Binary('125d7342b9ac11cd91a39af48aa17b4f63f175d3')
			);
			
			$this->assertEqual(
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('0102030405060708090a0b0c0d0e0f10111213141516171819'), 
					TextUtils::hex2Binary('cdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcd')
				),
				TextUtils::hex2Binary('4c9007f4026250c6bc8414f9bf50c86c2d7235da')
			);
			
			$this->assertEqual(
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c'), 
					"Test With Truncation"
				),
				TextUtils::hex2Binary('4c1a03424b55e07fe7f27be1d58bb9324a9a5a04')
			);
			
			$this->assertEqual(
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'), 
					"Test Using Larger Than Block-Size Key - Hash Key First"
				),
				TextUtils::hex2Binary('aa4ae5e15272d00e95705637ce8a3b55ed402112')
			);
			
			$this->assertEqual(
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'), 
					"Test Using Larger Than Block-Size Key and Larger Than One Block-Size Data"
				),
				TextUtils::hex2Binary('e8e99d0f45237d786d6bbaa7965c7808bbff1a91')
			);
		}
	}
	
	class RandomSourceStub implements RandomSource 
	{
		private $data = null;
		
		public function __construct($data)
		{
			$this->data = $data;
		}
		
		public function getBytes($numOfBytes)
		{
			return $this->data;
		}
	}
?>