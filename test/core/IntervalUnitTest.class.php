<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class IntervalUnitTest extends TestCase
	{
		public function testMicrosecond()
		{
			$unit = \Onphp\IntervalUnit::create('microsecond');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40')
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
			$unit = \Onphp\IntervalUnit::create('millisecond');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40')
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
			$unit = \Onphp\IntervalUnit::create('second');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40')
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
			$unit = \Onphp\IntervalUnit::create('minute');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40')
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
			$unit = \Onphp\IntervalUnit::create('hour');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40')
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
			$unit = \Onphp\IntervalUnit::create('hour');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40'),
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
			$unit = \Onphp\IntervalUnit::create('day');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40')
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
		
		public function testDayFloor()
		{
			$unit = \Onphp\IntervalUnit::create('day');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2008-06-18 15:42:42'),
				true
			);
			
			$this->assertEquals(
				'2008-06-19 00:00:00',
				$result->toString()
			);
			
			$this->assertEquals(
				$result->toString(),
				$unit->truncate($result, true)->toString()
			);
		}
		
		public function testDayMsdMsk()
		{
			$unit = \Onphp\IntervalUnit::create('day');
			
			for ($hour = 1; $hour < 24; ++$hour) {
				$result = $unit->truncate(
					\Onphp\Timestamp::create("2008-10-26 00:$hour:00")
				);
				
				$this->assertEquals(
					'2008-10-26 00:00:00',
					$result->toString()
				);
				
				$this->assertEquals(
					$result->toString(),
					$unit->truncate($result)->toString()
				);
				
				$result = $unit->truncate(
					\Onphp\Timestamp::create("2008-03-30 00:$hour:00"),
					true
				);
				
				$this->assertEquals(
					'2008-03-31 00:00:00',
					$result->toString()
				);
				
				$this->assertEquals(
					$result->toString(),
					$unit->truncate($result, true)->toString()
				);
			}
		}
		
		public function testWeek()
		{
			$unit = \Onphp\IntervalUnit::create('week');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40')
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
			$unit = \Onphp\IntervalUnit::create('week');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40'),
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
			$unit = \Onphp\IntervalUnit::create('month');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40')
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
			$unit = \Onphp\IntervalUnit::create('month');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40'),
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
			$unit = \Onphp\IntervalUnit::create('year');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40')
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
			$unit = \Onphp\IntervalUnit::create('year');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40'),
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
			$unit = \Onphp\IntervalUnit::create('decade');
			
			$result = $unit->truncate(
				\Onphp\Timestamp::create('2001-02-16 20:38:40')
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
			$unit = \Onphp\IntervalUnit::create('second');
			
			$this->assertEquals(
				4,
				$result = $unit->countInRange(
					\Onphp\TimestampRange::create(
						$start = \Onphp\Timestamp::create('2008-12-31 23:59:58'),
						$end = \Onphp\Timestamp::create('2009-01-01 00:00:02')
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
			$unit = \Onphp\IntervalUnit::create('hour');
			
			$this->assertEquals(
				4,
				$result = $unit->countInRange(
					\Onphp\TimestampRange::create(
						// In 2008, March, 30 is a 23h-day because of daylight
						// saving time
						$start = \Onphp\Timestamp::create('2008-03-30 01:30:00'),
						$end = \Onphp\Timestamp::create('2008-03-30 05:30:00')
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
			$unit = \Onphp\IntervalUnit::create('month');
			
			$this->assertEquals(
				6,
				$result = $unit->countInRange(
					\Onphp\TimestampRange::create(
						$start = \Onphp\Timestamp::create('2008-12-31 23:59:58'),
						$end = \Onphp\Timestamp::create('2009-05-28 03:00:00')
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
			$unit = \Onphp\IntervalUnit::create('month');
			
			$this->assertEquals(
				4,
				$result = $unit->countInRange(
					\Onphp\TimestampRange::create(
						$start = \Onphp\Timestamp::create('2008-12-31 23:59:58'),
						$end = \Onphp\Timestamp::create('2009-05-28 03:00:00')
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