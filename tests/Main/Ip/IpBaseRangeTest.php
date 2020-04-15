<?php

namespace OnPHP\Tests\Main\Ip;

use OnPHP\Core\DB\ImaginaryDialect;
use OnPHP\Core\DB\PgSQL;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Net\Ip\IpAddress;
use OnPHP\Main\Net\Ip\IpRange;
use OnPHP\Tests\TestEnvironment\TestCaseDB;

/**
 * @group ibr
 */
final class IpBaseRangeTest extends TestCaseDB
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
				$range->toDialectString($this->getDbByType(PgSQL::class)->getDialect())
			);

			$this->assertEquals(
				'192.168.1.1-192.168.255.255',
				$range->toDialectString(ImaginaryDialect::me())
			);
	}

	public function testCreation()
	{
		$range =
			IpRange::create('192.168.2.1-192.168.255.255');

		$anotherRange =
			IpRange::create(
				IpAddress::create('192.168.2.1'),
				IpAddress::create('192.168.255.255')
			);

		$this->assertEquals($range->toString(), $anotherRange->toString());

		try {
			$range =
				IpRange::create('192.168.2.1-192.168.255.666');

			$this->fail();
		} catch (WrongArgumentException $e) {/**/}

		try {
			$range =
				IpRange::create('192.168.666.1-192.168.255.254');

			$this->fail();
		} catch (WrongArgumentException $e) {/**/}

		try {
			$range =
				IpRange::create(array(array(array(false))));

			$this->fail();
		} catch (WrongArgumentException $e) {/**/}

		$slashRange = IpRange::create('192.168.1.0/30');

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
				IpRange::create('192.168.1.0/4');

			$this->fail();
		} catch (WrongArgumentException $e) {/**/}

		$range = IpRange::create('8.8/16');

		$this->assertEquals($range->toString(), '8.8.0.0-8.8.255.255');

		$range = IpRange::create('192.168.1.1');

		$this->assertEquals(
			$range->getStart()->toString(),
			$range->getEnd()->toString()
		);
	}
}
?>