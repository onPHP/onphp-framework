<?php
	final class DateUtilsTest extends PHPUnit_Framework_TestCase
	{
    	/**
     	* @dataProvider alignToSecondsDataProvider
     	*/
		public function testAlignToSeconds(Timestamp $stamp, $expected)
		{
			$this->assertEquals(
				DateUtils::alignToSeconds($stamp, 42)->toString(),
				$expected
			);
		}
		
		public function alignToSecondsDataProvider()
	    {
	        return array(
	          array(
	          	Timestamp::create('2009-01-01 10:00:42'),
	          	'2009-01-01 10:00:42'
	          ),
	          array(
	          	Timestamp::create('2009-01-01 10:00:41'),
	          	'2009-01-01 10:00:00'
	          ),
	          array(
				Timestamp::create('2009-01-01 10:01:34'),
	          	'2009-01-01 10:01:24'
	          ),
	          array(
				Timestamp::create('2009-01-01 10:10:01'),
	          	'2009-01-01 10:09:48'
	          )
	        );
	    }
	}
?>