<?php

namespace OnPHP\Tests\Main;

use OnPHP\Main\Net\Mail\MailEncoding;
use OnPHP\Main\Net\Mail\MimeMail;
use OnPHP\Main\Net\Mail\MimePart;
use OnPHP\Tests\TestEnvironment\TestCase;

final class MimeMailTest extends TestCase
{
	public function testMimeMail()
	{
		$mimeMail = new MimeMail();
		$mimeMail->setBoundary('MIME_MAIL_TEST');
		$mimeMail->addPart(
			MimePart::create()->
				setEncoding(MailEncoding::base64())->
				setCharset('UTF-8')->
				loadBodyFromFile(
					dirname(__FILE__).'/data/mimeMail/message.html'
				)->
				setContentType('text/html')
		);

		$mimeMail->addPart(
			MimePart::create()->
				setContentId('picture')->
				setEncoding(MailEncoding::base64())->
				setFilename('picture.jpg')->
				loadBodyFromFile(
					dirname(__FILE__).'/data/mimeMail/picture.jpg'
				)->
				setContentType('image/jpeg')
		);

		$mimeMail->build();

//			file_put_contents(dirname(__FILE__).'/data/mimeMail/headers.txt', $mimeMail->getHeaders());
//			file_put_contents(dirname(__FILE__).'/data/mimeMail/encodedBody.txt', $mimeMail->getEncodedBody());

		$this->assertEquals(
			$mimeMail->getHeaders(),
			file_get_contents(
				dirname(__FILE__).'/data/mimeMail/headers.txt'
			)
		);

		$this->assertEquals(
			$mimeMail->getEncodedBody(),
			file_get_contents(
				dirname(__FILE__).'/data/mimeMail/encodedBody.txt'
			)
		);
	}
}
?>