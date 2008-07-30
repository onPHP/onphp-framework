<?php
	/* $Id$ */
	
	final class IntervalUnitTest extends TestCase
	{
		public function testMicrosecond()
		{
			$unit = IntervalUnit::create('microsecond');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40')
			);
			
			$this->assertEquals(
				'2001-02-16 20:38:40',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result)->toString()
			);
		}
		
		public function testMillisecond()
		{
			$unit = IntervalUnit::create('millisecond');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40')
			);
			
			$this->assertEquals(
				'2001-02-16 20:38:40',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result)->toString()
			);
		}
		
		public function testSecond()
		{
			$unit = IntervalUnit::create('second');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40')
			);
			
			$this->assertEquals(
				'2001-02-16 20:38:40',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result)->toString()
			);
		}
		
		public function testMinute()
		{
			$unit = IntervalUnit::create('minute');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40')
			);
			
			$this->assertEquals(
				'2001-02-16 20:38:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result)->toString()
			);
		}
		
		public function testHour()
		{
			$unit = IntervalUnit::create('hour');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40')
			);
			
			$this->assertEquals(
				'2001-02-16 20:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result)->toString()
			);
		}
		
		public function testHourFloor()
		{
			$unit = IntervalUnit::create('hour');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40'),
				true
			);
			
			$this->assertEquals(
				'2001-02-16 21:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result, true)->toString()
			);
		}
		
		public function testDay()
		{
			$unit = IntervalUnit::create('day');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40')
			);
			
			$this->assertEquals(
				'2001-02-16 00:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result)->toString()
			);
		}
		
		public function testWeek()
		{
			$unit = IntervalUnit::create('week');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40')
			);
			
			$this->assertEquals(
				'2001-02-12 00:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result)->toString()
			);
		}
		
		public function testWeekFloor()
		{
			$unit = IntervalUnit::create('week');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40'),
				true
			);
			
			$this->assertEquals(
				'2001-02-19 00:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result, true)->toString()
			);
		}
		public function testMonth()
		{
			$unit = IntervalUnit::create('month');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40')
			);
			
			$this->assertEquals(
				'2001-02-01 00:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result)->toString()
			);
		}
		
		public function testMonthFloor()
		{
			$unit = IntervalUnit::create('month');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40'),
				true
			);
			
			$this->assertEquals(
				'2001-03-01 00:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result, true)->toString()
			);
		}
		
		public function testYear()
		{
			$unit = IntervalUnit::create('year');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40')
			);
			
			$this->assertEquals(
				'2001-01-01 00:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result)->toString()
			);
		}
		
		public function testYearFloor()
		{
			$unit = IntervalUnit::create('year');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40'),
				true
			);
			
			$this->assertEquals(
				'2002-01-01 00:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result, true)->toString()
			);
		}
		
		public function testDecade()
		{
			$unit = IntervalUnit::create('decade');
			
			$result = $unit->truncate(
				Timestamp::create('2001-02-16 20:38:40')
			);
			
			$this->assertEquals(
				'2000-01-01 00:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result)->toString()
			);
		}
		
		public function testCountSeconds()
		{
			$unit = IntervalUnit::create('second');
			
			$this->assertEquals(
				4,
				$result = $unit->countInRange(
					TimestampRange::create(
						$start = Timestamp::create('2008-12-31 23:59:58'),
						$end = Timestamp::create('2009-01-01 00:00:02')
					)
				)
			);
			
			$this->assertGreaterThanOrEqual(
				$end->toStamp(),
				$start->spawn($result.' '.$unit->getName())->toStamp()
			);
			
			$this->assertLessThanOrEqual(
				$unit->truncate($end, true)->toStamp(),
				$start->spawn(($result - 1).' '.$unit->getName())->toStamp()
			);
		}
		
		public function testCountHoursDST()
		{
			$unit = IntervalUnit::create('hour');
			
			$this->assertEquals(
				4,
				$result = $unit->countInRange(
					TimestampRange::create(
						// In 2008, March, 30 is a 23h-day because of daylight
						// saving time
						$start = Timestamp::create('2008-03-30 01:30:00'),
						$end = Timestamp::create('2008-03-30 05:30:00')
					)
				)
			);
			
			$this->assertGreaterThanOrEqual(
				$end->toStamp(),
				$start->spawn($result.' '.$unit->getName())->toStamp()
			);
			
			$this->assertLessThanOrEqual(
				$unit->truncate($end, true)->toStamp(),
				$start->spawn(($result - 1).' '.$unit->getName())->toStamp()
			);
		}
		
		public function testCountMonths()
		{
			$unit = IntervalUnit::create('month');
			
			$this->assertEquals(
				6,
				$result = $unit->countInRange(
					TimestampRange::create(
						$start = Timestamp::create('2008-12-31 23:59:58'),
						$end = Timestamp::create('2009-05-28 03:00:00')
					)
				)
			);
			
			$this->assertGreaterThanOrEqual(
				$end->toStamp(),
				$start->spawn($result.' '.$unit->getName())->toStamp()
			);
			
			$this->assertLessThanOrEqual(
				$unit->truncate($end, true)->toStamp(),
				$start->spawn(($result - 1).' '.$unit->getName())->toStamp()
			);
		}
		
		public function testCountMonthsNotOverlapped()
		{
			$unit = IntervalUnit::create('month');
			
			$this->assertEquals(
				4,
				$result = $unit->countInRange(
					TimestampRange::create(
						$start = Timestamp::create('2008-12-31 23:59:58'),
						$end = Timestamp::create('2009-05-28 03:00:00')
					),
					false
				)
			);
			
			$this->assertLessThanOrEqual(
				$end->toStamp(),
				$start->spawn($result.' '.$unit->getName())->toStamp()
			);
		}
	}
?>