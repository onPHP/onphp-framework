<?php
	final class CrypterTest extends TestCase
	{
		/**
		 * @dataProvider encryptionData
		**/
		public function testDecrypt($key, $data)
		{
			$ecryptedData = self::getCrypter()->encrypt($key, $data);
			
			$this->assertEquals(
				$data,
				self::getCrypter()->decrypt($key, $ecryptedData)
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

		protected function setUp()
		{
			if (!function_exists('mcrypt_module_open'))
				$this->markTestSkipped('You have no mcrypt ext. to test Crypter');
		}

		private static function getCrypter()
		{
			static $crypter;

			if (!$crypter)
				$crypter = Crypter::create(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);

			return $crypter;
		}
	}
?>