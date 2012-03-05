<?php
	/* $Id$ */
	
	final class DateTest extends TestCase
	{
		public function testDayDifference()
		{
			$today = Date::makeToday();
			
			$this->dayDifferenceTest($today, $today, 0);
			
			$timestamp = Timestamp::makeNow();
			
			$this->dayDifferenceTest($timestamp, $timestamp, 0);
			
			$left = Date::create('2008-01-12');
			$right = Date::create('2008-01-13');
			
			$this->dayDifferenceTest($left, $right, 1);
			
			$left = Date::create('2008-01-12');
			$right = Date::create('2009-01-13');
			
			$this->dayDifferenceTest($left, $right, 367);
			
			$left = Date::create('2008-01-12');
			$right = Date::create('2008-01-11');
			
			$this->dayDifferenceTest($left, $right, -1);
			
			$left = Timestamp::create('2008-01-12 01:23:00');
			$right = Timestamp::create('2008-01-13 13:01:17');
			
			$this->dayDifferenceTest($left, $right, 1);
			
			// change time from winter to summer
			$left = Timestamp::create('2008-03-29 02:00:00');
			$right = Timestamp::create('2008-03-30 02:00:00');
			
			$this->dayDifferenceTest($left, $right, 1);
			
			$left = Timestamp::create('2008-03-29 03:00:00');
			$right = Timestamp::create('2008-03-30 03:00:00');
			
			$this->dayDifferenceTest($left, $right, 1);

			$weekCount = Date::getWeekCountInYear(2012);
			$this->assertEquals($weekCount, 52, "Week count is incorrect");

			$dateFromWeekNumberStamp = Date::makeFromWeek(5, 2012)->toStamp();
			$expectedDate = Date::create('2012-01-30 00:00:00')->toStamp();
			$this->assertEquals($dateFromWeekNumberStamp, $expectedDate, 'Creating date from week number is incorrect.');
			
			// unsolved giv's case
			// $left = Timestamp::create('2008-10-25 03:00:00');
			// $right = Timestamp::create('2008-10-26 02:59:00');
			// $this->dayDifferenceTest($left, $right, 0);
			
			return $this;
		}
		
		public function testDateComparsion()
		{
			$date1 = Date::create(mktime(0, 0, 0, 1, 1, 2009));
			$date2 = Date::create(mktime(1, 0, 0, 1, 1, 2009));
			
			$this->assertEquals($date1, $date2);
			
			$this->assertEquals(
				Date::compare($date1, $date2),
				0
			);
			
			return $this;
		}

		public function testMakeFromWeek()
		{
			$this->makeFromWeekTest(Date::create('2009-12-28'));
			$this->makeFromWeekTest(Date::create('2010-01-04'));

			return $this;
		}

		public function testSleeping()
		{
			$date = new Date('1984-03-25');

			$serializedDate = serialize($date);

			$unserializedDate = unserialize($serializedDate);

			$this->assertEquals($date->getDay(), $unserializedDate->getDay());
			$this->assertEquals($date->getMonth(), $unserializedDate->getMonth());
			$this->assertEquals($date->getYear(), $unserializedDate->getYear());

			$this->assertEquals($date->getFirstDayOfWeek(), $unserializedDate->getFirstDayOfWeek());
		}
		
		private function dayDifferenceTest(Date $left, Date $right, $expected)
		{
			$this->assertEquals(Date::dayDifference($left, $right), $expected);
			
			return $this;
		}

		private function makeFromWeekTest(Date $date)
		{
			// day is monday?
			$this->assertEquals(date('w', $date->toStamp()), 1);

			$this->assertEquals(
				Date::makeFromWeek(
					date('W', $date->toStamp()),
					date('Y', $date->toStamp())
				),
				$date
			);

			return $this;
		}
	}
?>
