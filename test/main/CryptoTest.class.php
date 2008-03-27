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

	final class CryptoTest extends TestCase
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
			
			$this->assertEquals(
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
			$this->assertEquals(
				$pairA->getPublic()->toString(),
				'4'
			);
			$this->assertEquals(
				$pairA->getPrivate()->toString(),
				'2'
			);
			
			
			$sourceB = new RandomSourceStub("\x03");
			$pairB = DiffieHellmanKeyPair::generate($parameters, $sourceB);
			$this->assertEquals(
				$pairB->getPublic()->toString(),
				'8'
			);
			$this->assertEquals(
				$pairB->getPrivate()->toString(),
				'3'
			);
			
			$this->assertEquals(
				$pairA->makeSharedKey($pairB->getPublic())->toString(),
				'64'
			);
			
			$this->assertEquals(
				$pairB->makeSharedKey($pairA->getPublic())->toString(),
				'64'
			);
			
			$bigSource = new RandomSourceStub(
				$factory->
					makeNumber(
						'17620208266278770330305877401674539709763945430177869257076175454731875847774962345544017639615825508394434743684629375358533661943819685038381342979593382253449035990153308416178843539244884579493167694072720167536494654808080865723281709878280854033940718446755086284684724942649928096406688489109561821711'
					)->
					toBinary()
			);
			$bigPair = DiffieHellmanKeyPair::generate(
				DiffieHellmanParameters::create(
					$factory->makeNumber(2),
					$factory->makeNumber(
						'155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443'
					)
				),
				$bigSource
			);
			$this->assertEquals(
				$bigPair->getPrivate()->toString(),
				'17620208266278770330305877401674539709763945430177869257076175454731875847774962345544017639615825508394434743684629375358533661943819685038381342979593382253449035990153308416178843539244884579493167694072720167536494654808080865723281709878280854033940718446755086284684724942649928096406688489109561821711'
			);
			$this->assertEquals(
				$bigPair->getPublic()->toString(),
				'93611077906724276144355642962486685180052418906604011044062651816823973443710236445918597792417855982839842862368107478409559670530180271906446567838772164434838441509602151691228515001241179534817267036102715381057132924139174991783818894647585751956892566340543905490069919079555140420098531372841096272473'
			);
			$this->assertEquals(
				$bigPair->makeSharedKey(
						$factory->makeFromBinary(
							base64_decode(
								'ALOlru0GPBCbWulLlZPjRFCVPQDOnmQ+bUaowbHvgA4D1TEDlHA0WgX+HnQuq3KleYWK8jgY0nH/l02gdE93OCMq1Kitat+I8PE1HGVkAQ1J7pfM6f3WISSCa88xm63CLVg4MPTCP+0ONh6A5XUkN+D+LwS4ff9zUoF9GVRRKN6K'
							)
						)
					)->
					toString(),
				'130574307951871152424428936775752636435856388421474727121158044023326117405721624660812283811910707382094169797795608545580344115985070696700827335362669644864287947205210570090276035970957788252377653229959968013235712431298868299742980604595915492149293770517689028200287095518234357514745938567136863374034'
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
		**/
		public function testHmacsha1()
		{
			$this->assertEquals(
				TextUtils::hex2Binary('b617318655057264e28bc0b6fb378c8ef146be00'),
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b'),
					"Hi There"
				)
			);
			
			$this->assertEquals(
				CryptoFunctions::hmacsha1(
					"Jefe",
					"what do ya want for nothing?"
				),
				TextUtils::hex2Binary('effcdf6ae5eb2fa2d27416d5f184df9c259a7c79')
			);
			
			$this->assertEquals(
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'),
					TextUtils::hex2Binary('dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd')
				),
				TextUtils::hex2Binary('125d7342b9ac11cd91a39af48aa17b4f63f175d3')
			);
			
			$this->assertEquals(
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('0102030405060708090a0b0c0d0e0f10111213141516171819'),
					TextUtils::hex2Binary('cdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcd')
				),
				TextUtils::hex2Binary('4c9007f4026250c6bc8414f9bf50c86c2d7235da')
			);
			
			$this->assertEquals(
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c'),
					"Test With Truncation"
				),
				TextUtils::hex2Binary('4c1a03424b55e07fe7f27be1d58bb9324a9a5a04')
			);
			
			$this->assertEquals(
				CryptoFunctions::hmacsha1(
					TextUtils::hex2Binary('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'),
					"Test Using Larger Than Block-Size Key - Hash Key First"
				),
				TextUtils::hex2Binary('aa4ae5e15272d00e95705637ce8a3b55ed402112')
			);
			
			$this->assertEquals(
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