<?php

namespace OnPHP\Tests\Main;

use OnPHP\Main\Crypto\Crypter;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group utils
 * @group crypto
 */
final class CrypterTest extends TestCase
{
	/**
	 * @dataProvider encryptionData
	**/
	public function testDecrypt($key, $data)
	{
		$ecryptedData = Crypter::encrypt($key, $data);

		$this->assertEquals(
			$data,
			Crypter::decrypt($key, $ecryptedData)
		);
	}

	public static function encryptionData()
	{
		return
			array(
				// array(key, data)
				array('secret', 'my data'),
				array('122222222212csfsfsf1111111111111111 long key', "\neeeeeeeeDDDD\r\n"),
				array('kkey', "\n\n\n\n000\t")
			);
	}

	protected function setUp(): void
	{
		if (!function_exists('openssl_encrypt'))
			$this->markTestSkipped('You have no OpenSSL library to test Crypter');
	}
}
?>