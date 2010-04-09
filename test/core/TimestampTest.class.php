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
		}
		
		public function testInvalidTimestamp()
		{
			try {
				new Timestamp('2007-0-0');
				$this->fail();
			} catch (WrongArgumentException $e) {
				/* pass */
			}
			
			try {
				new Timestamp('2007-00-00');
				$this->fail();
			} catch (WrongArgumentException $e) {
				/* pass */
			}
			
			try {
				new Timestamp('2007-01-00');
				$this->fail();
			} catch (WrongArgumentException $e) {
				/* pass */
			}
			
			try {
				new Timestamp('2007-00-01');
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

		public function testStartHour()
		{
			$stamp = Timestamp::create('2010-03-25 14:15:10');
			
			$this->assertNotEquals(
				$stamp->toStamp(),
				$stamp->getHourStartStamp()
			);
			
			$this->assertTrue(
				Timestamp::create($stamp->getHourStartStamp())
				instanceof Timestamp
			);
			
			$this->assertEquals(
				Timestamp::create($stamp->getHourStartStamp())->toString(),
				'2010-03-25 14:00:00'
			);
		}
	}
?>