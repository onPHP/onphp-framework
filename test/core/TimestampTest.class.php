<?php
	/* $Id$ */
	
	final class TimestampTest extends TestCase
	{
		public function testNonEpoch()
		{
			$future = '4683-03-04';
			$after = new Timestamp($future);
			
			$this->assertEquals($after->getDay(), '04');
			$this->assertEquals($after->getMonth(), '03');
			$this->assertEquals($after->getYear(), '4683');
			
			$past = '1234-04-03';
			$before = new Timestamp($past);
			
			$this->assertEquals($before->getDay(), '03');
			$this->assertEquals($before->getMonth(), '04');
			$this->assertEquals($before->getYear(), '1234');
			
			$this->assertFalse($after->equals($before));
			
			$this->assertEquals($future, $after->toDate());
			$this->assertEquals($past, $before->toDate());
			
			$time = ' 00:00.00';
			$this->assertEquals($future.$time, $after->toDateTime());
			$this->assertEquals($past.$time, $before->toDateTime());
			
			try {
				new Timestamp('2007-0-0');
				$this->fail();
			} catch (WrongArgumentException $e) {
				/* pass */
			}
			
			try {
				new Timestamp('2008-07-40 14:30:24');
				die();
				$this->fail();
			} catch (WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function testCornerCases()
		{
			try {
				Date::create('2007-10-0');
				
				$this->fail();
			} catch (WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function testFromWeekCreation()
		{
			$date = Date::makeFromWeek(2, 2008);
			
			$this->assertEquals($date->toString(), '2008-01-07');
		}
		
		public function testDateHelpers()
		{
			$first = Date::create('2008-08-08');
			$second = $first->spawn('-1 week');
			
			$this->assertEquals(
				$second, $second->spawn()
			);
			
			try {
				$second->modify('dunno to what time');
				
				$this->fail();
			} catch (WrongArgumentException $e) {/*_*/}
			
			if (!extension_loaded('calendar')) {
				try {
					dl('calendar');
				} catch (BaseException $e) {
					/*_*/
				}
			}
			
			if (extension_loaded('calendar')) {
				$this->assertEquals(
					Date::dayDifference($second, $first),
					7
				);
			}
			
			$this->assertEquals(
				Date::compare($first, $second),
				1
			);
			
			$this->assertEquals(
				$first->getWeek() - $second->getWeek(),
				1
			);
			
			$this->assertEquals($first->getFirstDayOfWeek()->getDay(), 4);
			$this->assertEquals($second->getFirstDayOfWeek()->getDay(), 28);
			
			$this->assertEquals($first->getLastDayOfWeek()->getDay(), 10);
			$this->assertEquals($second->getLastDayOfWeek()->getDay(), 3);
			
			$this->assertEquals(
				$first->toString(),
				$first->toDialectString(ImaginaryDialect::me())
			);
			
			$this->assertEquals(
				$first->toIsoString(),
				$first->toDialectString(ImaginaryDialect::me())
			);
		}
		
		public function testTimestampHelpers()
		{
			$this->assertEquals(
				Timestamp::makeNow()->toDate(),
				Timestamp::makeToday()->toDate()
			);
			
			$ts = Timestamp::create('2008-08-08 1:2:3');
			
			$this->assertEquals($ts->getHour(), 1);
			$this->assertEquals($ts->getMinute(), 2);
			$this->assertEquals($ts->getSecond(), 3);
			
			$this->assertEquals(
				$ts->getDayStartStamp(),
				Timestamp::create($ts->getDayStartStamp())->getDayStartStamp()
			);
			
			$this->assertEquals($ts->toIsoString(true), '2008-08-07T21:02:03Z');
			$this->assertRegExp(
				'/^2008\-08\-[\d]{2}T[\d]{2}\:[\d]{2}\:[\d]{2}\+[\d]{4}$/',
				$ts->toIsoString(false)
			);
		}
		
		public static function timeProvider()
		{
			return array(
				array(1, 1, 0, 0),
				array(12, 12, 0, 0),
				array(123, 12, 30, 0),
				array(256, 2, 56, 0),
				array(1234, 12, 34, 0),
				array(12345, 12, 34, 5),
				array(123456, 12, 34, 56),
				array('12:34', 12, 34, 0),
				array('12:34:56', 12, 34, 56)
			);
		}
		
		/**
		 * @dataProvider timeProvider
		**/
		public function testTimeParsing($input, $hour, $minute, $second)
		{
			$time = new Time($input);
			
			$this->assertEquals($time->getHour(), $hour);
			$this->assertEquals($time->getMinute(), $minute);
			$this->assertEquals($time->getSecond(), $second);
			
			$this->assertEquals(
				$time->toTimeString(),
				sprintf('%02d:%02d', $time->getHour(), $time->getMinute())
			);
			
			$this->assertEquals(
				$time->toString(),
				sprintf(
					'%02d:%02d:%02d',
					$time->getHour(), $time->getMinute(), $time->getSecond()
				)
			);
			
			$this->assertEquals(
				round($time->toSeconds() / $time->toMinutes()),
				60
			);
		}
		
		public function testTime()
		{
			try {
				$time = new Time('not really');
				
				$this->fail();
			} catch (WrongArgumentException $e) {/*_*/}
		}
		
		public function testTimestampNow()
		{
			try {
				Timestamp::create('now');
			} catch (WrongArgumentException $e) {
				$this->fail($e->getMessage());
			}
		}
		
		public function testDateNow()
		{
			try {
				Date::create('now');
			} catch (WrongArgumentException $e) {
				$this->fail($e->getMessage());
			}
		}
	}
?>