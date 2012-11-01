<?php
	/**
	 * @group ibr
	 */
	namespace Onphp\Test;

	final class IpBaseRangeTest extends TestCaseDB
	{
		public function testContains()
		{
			$ipRange =
				\Onphp\IpRange::create(
					\Onphp\IpAddress::create('127.0.0.1'),
					\Onphp\IpAddress::create('127.0.0.10')
				);
			
			$this->assertTrue(
				$ipRange->contains(
					\Onphp\IpAddress::create(
						'127.0.0.1'
					)
				)
			);
			
			$this->assertTrue(
				$ipRange->contains(
					\Onphp\IpAddress::create(
						'127.0.0.9'
					)
				)
			);
			
			$this->assertTrue(
				$ipRange->contains(
					\Onphp\IpAddress::create(
						'127.0.0.10'
					)
				)
			);
			
			$this->assertFalse(
				$ipRange->contains(
					\Onphp\IpAddress::create(
						'127.0.0.0'
					)
				)
			);
			
			$this->assertFalse(
				$ipRange->contains(
					\Onphp\IpAddress::create(
						'127.0.0.11'
					)
				)
			);
			
			$this->assertFalse(
				$ipRange->contains(
					\Onphp\IpAddress::create(
						'127.0.0.255'
					)
				)
			);
		}
		
		public function testToString()
		{
			$range =
				\Onphp\IpRange::create(
					\Onphp\IpAddress::create('192.168.1.1'),
					\Onphp\IpAddress::create('192.168.255.255')
				);
			
				$this->assertEquals(
					'192.168.1.1-192.168.255.255',
					$range->toString()
				);
				
				$this->assertEquals(
					'\'192.168.1.1-192.168.255.255\'',
					$range->toDialectString($this->getDbByType('\Onphp\PgSQL')->getDialect())
				);
				
				$this->assertEquals(
					'192.168.1.1-192.168.255.255',
					$range->toDialectString(\Onphp\ImaginaryDialect::me())
				);
		}
		
		public function testCreation()
		{
			$range =
				\Onphp\IpRange::create('192.168.2.1-192.168.255.255');
			
			$anotherRange =
				\Onphp\IpRange::create(
					\Onphp\IpAddress::create('192.168.2.1'),
					\Onphp\IpAddress::create('192.168.255.255')
				);
			
			$this->assertEquals($range->toString(), $anotherRange->toString());
			
			try {
				$range =
					\Onphp\IpRange::create('192.168.2.1-192.168.255.666');
				
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {/**/}
			
			try {
				$range =
					\Onphp\IpRange::create('192.168.666.1-192.168.255.254');
				
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {/**/}
			
			try {
				$range =
					\Onphp\IpRange::create(array(array(array(false))));
				
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {/**/}
			
			$slashRange = \Onphp\IpRange::create('192.168.1.0/30');
			
			$this->assertEquals(
				'192.168.1.0',
				$slashRange->getStart()->toString()
			);
			
			$this->assertEquals(
				'192.168.1.3',
				$slashRange->getEnd()->toString()
			);
			
			try {
				$range =
					\Onphp\IpRange::create('192.168.1.0/4');
				
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {/**/}
			
			$range = \Onphp\IpRange::create('8.8/16');
			
			$this->assertEquals($range->toString(), '8.8.0.0-8.8.255.255');
			
			$range = \Onphp\IpRange::create('192.168.1.1');
			
			$this->assertEquals(
				$range->getStart()->toString(),
				$range->getEnd()->toString()
			);
		}
	}
?>