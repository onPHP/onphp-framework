<?php
	/* $Id$ */
	
	final class IpUtilsTest extends TestCase
	{
		/**
		 * @dataProvider ips
		**/
		public function testMakeRanges($ips, $ranges)
		{
			$this->assertEquals($ranges, IpUtils::makeRanges($ips));
		}
		
		public static function ips()
		{
			return array(
				array(
					array(
						'10.1.1.1',
						'10.1.1.2',
						'10.1.10.0',
						'10.1.9.255',
						'10.1.9.254'
					),
					array(
						array(
							'10.1.1.1',
							'10.1.1.2'
						),
						array(
							'10.1.9.254',
							'10.1.9.255',
							'10.1.10.0'
						)
					)
				)
			);
		}
	}
?>