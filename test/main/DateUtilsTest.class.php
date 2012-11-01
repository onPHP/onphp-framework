<?php
	namespace Onphp\Test;

	final class DateUtilsTest extends \PHPUnit_Framework_TestCase
	{
		/**
		 * @dataProvider alignToSecondsDataProvider
		**/
		public function testAlignToSeconds(\Onphp\Timestamp $stamp, $expected)
		{
			$this->assertEquals(
				\Onphp\DateUtils::alignToSeconds($stamp, 42)->toString(),
				$expected
			);
		}
		
		public static function alignToSecondsDataProvider()
		{
			return array(
				array(
					\Onphp\Timestamp::create('2009-01-01 10:00:42'),
					'2009-01-01 10:00:42'
				),
				array(
					\Onphp\Timestamp::create('2009-01-01 10:00:41'),
					'2009-01-01 10:00:00'
				),
				array(
					\Onphp\Timestamp::create('2009-01-01 10:01:34'),
					'2009-01-01 10:01:24'
				),
				array(
					\Onphp\Timestamp::create('2009-01-01 10:10:01'),
					'2009-01-01 10:09:48'
				)
			);
		}
	}
?>