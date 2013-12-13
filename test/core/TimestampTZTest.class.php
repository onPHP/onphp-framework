<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class TimestampTZTest extends TestCase
	{
		public function testDifferentZones()
		{
			$someDate = \Onphp\TimestampTZ::create('2011-01-01 12:10:10 Europe/Moscow');
			$this->assertEquals(
				'2011-01-01 12:10:10',
				$someDate->toTimestamp('Europe/Moscow')->toString()
			);

			$this->assertEquals(
				'2011-01-01 09:10:10',
				$someDate->toTimestamp('Europe/London')->toString()
			);
			
			$moscowTime = \Onphp\TimestampTZ::create('2011-01-01 00:00:00 Europe/Moscow');
			$londonTime = \Onphp\TimestampTZ::create('2010-12-31 21:00:00 Europe/London');
			
			$this->assertEquals(0, \Onphp\TimestampTZ::compare($moscowTime, $londonTime));
			
			$moscowTime->modify('+ 1 second');
			$this->assertEquals(1, \Onphp\TimestampTZ::compare($moscowTime, $londonTime));
			$moscowTime->modify('- 2 second');
			$this->assertEquals(-1, \Onphp\TimestampTZ::compare($moscowTime, $londonTime));
			
			
			$this->assertEquals(
				'2010-12-31 23:59:59',
				$moscowTime->toTimestamp('Europe/Moscow')->toString()
			);
		}
		
		/**
		 * @group ff
		 */
		public function testDialect()
		{
			//setup
			$someDate = \Onphp\TimestampTZ::create('2012-02-23 12:12:12 GMT');
			//expectation
			$expectation = $someDate->toTimestamp()->toString();
			
			//check
			$this->assertEquals(
				$someDate->toDialectString(\Onphp\ImaginaryDialect::me()),
				$expectation
			);
		}
		
		/**
		 * @group ff
		 */
		public function testSleeping() {
			$time = \Onphp\TimestampTZ::create('2011-03-08 12:12:12 PST');
			$sleepedTime = unserialize(serialize($time));
			
			$this->assertEquals(\Onphp\TimestampTZ::compare($time, $sleepedTime), 0);
		}
	}
?>