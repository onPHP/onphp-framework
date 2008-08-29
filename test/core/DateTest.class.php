<?php
	/* $Id$ */
	
	final class DateTest extends TestCase
	{
		public function testDayDifference()
		{
			$today = Date::makeToday();
			
			$this->assertEquals(
				Date::dayDifference($today, $today),
				$this->oldDayDifference($today, $today)
			);
			
			$this->assertEquals(Date::dayDifference($today, $today), 0);
			
			$timestamp = Timestamp::makeNow();
			
			$this->assertEquals(
				Date::dayDifference($timestamp, $timestamp),
				$this->oldDayDifference($timestamp, $timestamp)
			);
			
			$this->assertEquals(Date::dayDifference($timestamp, $timestamp), 0);
			
			$left = Date::create('2008-01-12');
			$right = Date::create('2008-01-13');
			
			$this->assertEquals(
				Date::dayDifference($left, $right),
				$this->oldDayDifference($left, $right)
			);
			
			$this->assertEquals(Date::dayDifference($left, $right), 1);
			
			$left = Date::create('2008-01-12');
			$right = Date::create('2009-01-13');
			
			$this->assertEquals(
				Date::dayDifference($left, $right),
				$this->oldDayDifference($left, $right)
			);
			
			$this->assertEquals(Date::dayDifference($left, $right), 367);
			
			$left = Date::create('2008-01-12');
			$right = Date::create('2008-01-11');
			
			$this->assertEquals(
				Date::dayDifference($left, $right),
				$this->oldDayDifference($left, $right)
			);
			
			$this->assertEquals(Date::dayDifference($left, $right), -1);
			
			$left = Timestamp::create('2008-01-12 01:23:00');
			$right = Timestamp::create('2008-01-13 13:01:17');
			
			$this->assertEquals(
				Date::dayDifference($left, $right),
				$this->oldDayDifference($left, $right)
			);
			
			$this->assertEquals(Date::dayDifference($left, $right), 1);
		}
		
		private function oldDayDifference(Date $left, Date $right)
		{
			return
				gregoriantojd(
					$right->getMonth(),
					$right->getDay(),
					$right->getYear()
				)
				- gregoriantojd(
					$left->getMonth(),
					$left->getDay(),
					$left->getYear()
				);
		}
	}
?>