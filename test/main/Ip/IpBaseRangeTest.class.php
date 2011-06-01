<?php
	/* $Id$ */
	
	final class IpBaseRangeTest extends TestCase
	{
		public function testContains()
		{
			$ipRange =
				IpRange::create(
					IpAddress::create('127.0.0.1'),
					IpAddress::create('127.0.0.10')
				);
			
			$this->assertTrue(
				$ipRange->contains(
					IpAddress::create(
						'127.0.0.1'
					)
				)
			);
			
			$this->assertTrue(
				$ipRange->contains(
					IpAddress::create(
						'127.0.0.9'
					)
				)
			);
			
			$this->assertTrue(
				$ipRange->contains(
					IpAddress::create(
						'127.0.0.10'
					)
				)
			);
			
			$this->assertFalse(
				$ipRange->contains(
					IpAddress::create(
						'127.0.0.0'
					)
				)
			);
			
			$this->assertFalse(
				$ipRange->contains(
					IpAddress::create(
						'127.0.0.11'
					)
				)
			);
			
			$this->assertFalse(
				$ipRange->contains(
					IpAddress::create(
						'127.0.0.255'
					)
				)
			);
		}
		
		public function testToString()
		{
			$range =
				IpRange::create(
					IpAddress::create('192.168.1.1'),
					IpAddress::create('192.168.255.255')
				);
			
				$this->assertEquals(
					'192.168.1.1-192.168.255.255',
					$range->toString()
				);
				
				$this->assertEquals(
					'\'192.168.1.1-192.168.255.255\'',
					$range->toDialectString(PostgresDialect::me())
				);
				
				$this->assertEquals(
					'192.168.1.1-192.168.255.255',
					$range->toDialectString(ImaginaryDialect::me())
				);
		}
	}
?>