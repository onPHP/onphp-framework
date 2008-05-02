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
		
		public function testHelpers()
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
	}
?>