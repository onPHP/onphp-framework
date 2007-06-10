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
	}
?>