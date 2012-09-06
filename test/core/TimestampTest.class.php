<?php
	/* $Id$ */
	
	final class TimestampTest extends TestCase
	{
		public function testNonEpoch()
		{
			$future = '4683-03-04';
			$after = new Timestamp($future);
			
			$this->assertEquals('04', $after->getDay());
			$this->assertEquals('03', $after->getMonth());
			$this->assertEquals('4683', $after->getYear());
			
			$past = '1234-04-03';
			$before = new Timestamp($past);
			
			$this->assertEquals('03', $before->getDay());
			$this->assertEquals('04', $before->getMonth());
			$this->assertEquals('1234', $before->getYear());
			
			$this->assertFalse($after->equals($before));
			
			$this->assertEquals($future, $after->toDate());
			$this->assertEquals($past, $before->toDate());
			
			$time = ' 00:00.00';
			$this->assertEquals($future.$time, $after->toDateTime());
			$this->assertEquals($past.$time, $before->toDateTime());

			$past = '1-04-03';
			$before = new Timestamp($past);

			$this->assertEquals('03', $before->getDay());
			$this->assertEquals('04', $before->getMonth());
			$this->assertEquals(
				substr(date('Y', time()), 0, 2).'01',
				$before->getYear()
			);

			$past = '14-01-02';
			$before = new Timestamp($past);

			$this->assertEquals('02', $before->getDay());
			$this->assertEquals('01', $before->getMonth());
			$this->assertEquals(
				substr(date('Y', time()), 0, 2).'14',
				$before->getYear()
			);

			$past = '113-01-02';
			$before = new Timestamp($past);

			$this->assertEquals('02', $before->getDay());
			$this->assertEquals('01', $before->getMonth());
			$this->assertEquals(
				'0113',
				$before->getYear()
			);
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

		public function testSleeping()
		{
			$stamp = Timestamp::makeNow();

			$serializedStamp = serialize($stamp);

			$unserializedStamp = unserialize($serializedStamp);

			$this->assertEquals($stamp->getDay(), $unserializedStamp->getDay());
			$this->assertEquals($stamp->getMonth(), $unserializedStamp->getMonth());
			$this->assertEquals($stamp->getYear(), $unserializedStamp->getYear());
			
			$this->assertEquals($stamp->getMinute(), $unserializedStamp->getMinute());
			$this->assertEquals($stamp->getSecond(), $unserializedStamp->getSecond());

			$stamp = Timestamp::create('2039-01-05 12:14:05 Europe/Moscow');

			$serializedStamp = serialize($stamp);

			$unserializedStamp = unserialize($serializedStamp);

			$this->assertEquals($stamp->getDay(), $unserializedStamp->getDay());
			$this->assertEquals($stamp->getMonth(), $unserializedStamp->getMonth());
			$this->assertEquals($stamp->getYear(), $unserializedStamp->getYear());

			$this->assertEquals($stamp->getMinute(), $unserializedStamp->getMinute());
			$this->assertEquals($stamp->getSecond(), $unserializedStamp->getSecond());

			$stamp = Timestamp::create('1899-12-31 16:07:45 Europe/London');

			$serializedStamp = serialize($stamp);

			$unserializedStamp = unserialize($serializedStamp);

			$this->assertEquals($stamp->getDay(), $unserializedStamp->getDay());
			$this->assertEquals($stamp->getMonth(), $unserializedStamp->getMonth());
			$this->assertEquals($stamp->getYear(), $unserializedStamp->getYear());

			$this->assertEquals($stamp->getMinute(), $unserializedStamp->getMinute());
			$this->assertEquals($stamp->getSecond(), $unserializedStamp->getSecond());
		}
	}
?>