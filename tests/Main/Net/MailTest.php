<?php
/***************************************************************************
 *   Copyright (C) 2009-2010 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Tests\Main\Net;

use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Net\Mail\Mail;
use OnPHP\Main\Net\Mail\MailAddress;
use OnPHP\Main\Net\Mail\MailNotSentException;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group main
 * @group http
 * @group mail
 */
final class MailTest extends TestCase
{
	public function testMailAddressWithoutPerson()
	{
		$address = MailAddress::create()->
			setAddress('vasya@example.com');

		$this->assertEquals(
			'vasya@example.com',
			$address->toString()
		);
	}

	public function testMailAddressWithPerson()
	{
		$address = MailAddress::create()->
			setAddress('vasya@example.com')->
			setPerson('Vasya Pupkin');

		$this->assertEquals(
			'Vasya Pupkin <vasya@example.com>',
			$address->toString()
		);
	}

	public function testMailAddressWithQuotedPerson()
	{
		$address = MailAddress::create()->
			setAddress('vasya@example.com')->
			setPerson('!@#$%^&*()_+');

		$this->assertEquals(
			'"!@#$%^&*()_+" <vasya@example.com>',
			$address->toString()
		);
	}

	public function testMailAddressUnicode()
	{
		$address = MailAddress::create()->
			setAddress('vasya@example.com')->
			setPerson('Вася Пупкин');

		$this->assertEquals(
			'=?UTF-8?B?0JLQsNGB0Y8g0J/Rg9C/0LrQuNC9?= <vasya@example.com>',
			$address->toString()
		);
	}

	public function testMailAddressUnicodeLong()
	{
		$address = MailAddress::create()->
			setAddress('vasya@example.com')->
			setPerson('Ваня Пупкин Ваня Пупкин Ваня Пупкин Ваня Пупкин');

		$this->assertEquals(
			'=?UTF-8?B?0JLQsNC90Y8g0J/Rg9C/0LrQuNC9INCS0LDQvdGPINCf0YPQv9C60LjQvSA=?='
			."\r\n "
			.'=?UTF-8?B?0JLQsNC90Y8g0J/Rg9C/0LrQuNC9INCS0LDQvdGPINCf0YPQv9C60LjQvQ==?= <vasya@example.com>',
			$address->toString()
		);
	}
	
	public function testBadMailAddressesFirstCase()
	{
		$this->expectException(WrongArgumentException::class);
		
		MailAddress::create()->
			setAddress("va\004sya@example.com")->
			toString();
	}
	
	

	public function testBadMailAddressesSecondCase()
	{
		$this->expectException(WrongArgumentException::class);
		
		MailAddress::create()->
			setAddress("va sya@example.com")->
			toString();
	}

	public function testSendWithoutReturnPath()
	{
		// Will pass only if MTA not installed
		$this->expectException(MailNotSentException::class);
		
		Mail::create()->
			setTo('admin@localhost')->
			send();
	}
}
?>