<?php
	/* $Id$ */
	
	final class TextUtilsTest extends UnitTestCase
	{
		public function testFriendlyFileSize()
		{
			$units = array('', 'k' , 'M', 'G', 'T', 'P');
			
			$this->assertEqual(TextUtils::friendlyFileSize(0), '0');
			$this->assertEqual(TextUtils::friendlyFileSize(1024), '1k');
			$this->assertEqual(TextUtils::friendlyFileSize(812), '812');
			
			for ($i = 0; $i < 6; $i++) {
				$this->assertEqual(
					TextUtils::friendlyFileSize(2 * pow(1024, $i)), '2'.$units[$i]
				);
			}
			
			$this->assertEqual(
				TextUtils::friendlyFileSize(2 * pow(1024, 6)), '2048'.$units[5]
			);
		}
		
		public function testNormalizeUri()
		{
			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/'),
				'http://example.com/'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com'),
				'http://example.com/'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com:/'),
				'http://example.com/'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com:80/'),
				'http://example.com/'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://wWw.exaMPLE.COm/'),
				'http://www.example.com/'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('htTP://example.com/'),
				'http://example.com/'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/foo%2cbar'),
				'http://example.com/foo%2Cbar'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/foo%2Dbar%2dbaz'),
				'http://example.com/foo-bar-baz'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/a/b/c/./../../g'),
				'http://example.com/a/g'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/mid/content=5/../6'),
				'http://example.com/mid/6'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/a/./b'),
				'http://example.com/a/b'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/a/../b'),
				'http://example.com/b'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/../b'),
				'http://example.com/b'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/a/.'),
				'http://example.com/a/'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/a/..'),
				'http://example.com/'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/a/./'),
				'http://example.com/a/'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('http://example.com/a/../'),
				'http://example.com/'
			);

			$this->assertEqual(
				TextUtils::normalizeUri('hTTPS://a/./b/../b/%63/%7bfoo%7d'),
				'https://a/b/c/%7Bfoo%7D'
			);
			
			try {
				TextUtils::normalizeUri('http:/foo');
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
		}
	}
?>