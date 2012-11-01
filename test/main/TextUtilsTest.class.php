<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class TextUtilsTest extends TestCase
	{
		public function testFriendlyFileSize()
		{
			$units = array('', 'k' , 'M', 'G', 'T', 'P');
			
			$this->assertEquals(\Onphp\TextUtils::friendlyFileSize(0), '0');
			$this->assertEquals(\Onphp\TextUtils::friendlyFileSize(1024), '1k');
			$this->assertEquals(\Onphp\TextUtils::friendlyFileSize(812), '812');
			
			for ($i = 0; $i < 6; ++$i) {
				$this->assertEquals(
					\Onphp\TextUtils::friendlyFileSize(2 * pow(1024, $i)), '2'.$units[$i]
				);
			}
			
			$this->assertEquals(
				\Onphp\TextUtils::friendlyFileSize(2 * pow(1024, 6)), '2048'.$units[5]
			);
		}
		
		public function testFriendlyNumber()
		{
			$localeInfo = localeconv();
			$decimalPoint = $localeInfo['decimal_point'];
			
			$this->assertEquals(
				\Onphp\TextUtils::friendlyNumber(100.02), '100'.$decimalPoint.'02'
			);
			
			$this->assertEquals(\Onphp\TextUtils::friendlyNumber((0.1 + 0.7) * 10), '8');
			$this->assertEquals(\Onphp\TextUtils::friendlyNumber(10000), '10 000');

			$this->assertEquals(
				\Onphp\TextUtils::friendlyNumber(10000.45), '10 000'.$decimalPoint.'45'
			);

			$this->assertEquals(
				\Onphp\TextUtils::friendlyNumber(-999999.99), '-999 999'.$decimalPoint.'99'
			);
		}
		
		public function testNormalizeUri()
		{
			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/'),
				'http://example.com/'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com'),
				'http://example.com/'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com:/'),
				'http://example.com/'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com:80/'),
				'http://example.com/'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://wWw.exaMPLE.COm/'),
				'http://www.example.com/'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('htTP://example.com/'),
				'http://example.com/'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/foo%7bbar'),
				'http://example.com/foo%7Bbar'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/foo%2Dbar%2dbaz%2cqaz'),
				'http://example.com/foo-bar-baz%2Cqaz'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/a/b/c/./../../g'),
				'http://example.com/a/g'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/mid/content=5/../6'),
				'http://example.com/mid/6'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/a/./b'),
				'http://example.com/a/b'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/a/../b'),
				'http://example.com/b'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/../b'),
				'http://example.com/b'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/a/.'),
				'http://example.com/a/'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/a/..'),
				'http://example.com/'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/a/./'),
				'http://example.com/a/'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('http://example.com/a/../'),
				'http://example.com/'
			);

			$this->assertEquals(
				\Onphp\TextUtils::normalizeUri('hTTPS://a/./b/../b/%63/%7bfoo%7d'),
				'https://a/b/c/%7Bfoo%7D'
			);
		}
		
		public function testHex2Binary()
		{
			$this->assertEquals(
				'     ',
				\Onphp\TextUtils::hex2Binary('2020202020')
			);
		}
	}
?>