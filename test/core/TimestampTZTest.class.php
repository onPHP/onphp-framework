<?php
/* $Id$ */

final class TimestampTZTest extends TestCase
{
	/**
	 * @group ff
	 */
	public function testDifferentZones()
	{
		$someDate = TimestampTZ::create('2011-01-01 12:10:10 Europe/Moscow');
		$this->assertEquals(
			'2011-01-01 12:10:10+0300',
			$someDate->toTimestamp('Europe/Moscow')->toString()
		);

		$this->assertEquals(
			$someDate->toTimestamp('Europe/London')->toString(),
			'2011-01-01 09:10:10+0000'
		);

		$moscowTime = TimestampTZ::create('2011-01-01 00:00:00 Europe/Moscow');
		$londonTime = TimestampTZ::create('2010-12-31 21:00:00 Europe/London');

		$this->assertEquals(0, TimestampTZ::compare($moscowTime, $londonTime));

		$moscowTime->modify('+ 1 second');
		$this->assertEquals(TimestampTZ::compare($moscowTime, $londonTime), 1);
		$moscowTime->modify('- 2 second');
		$this->assertEquals(TimestampTZ::compare($moscowTime, $londonTime), -1);


		$this->assertEquals(
			$moscowTime->toTimestamp('Europe/Moscow')->toString(),
			'2010-12-31 23:59:59+0300'
		);
	}

	/**
	 * @group ff
	 */
	public function testDialect()
	{
		//setup
		$someDate = TimestampTZ::create('2012-02-23 12:12:12 GMT');
		//expectation
		$expectation = $someDate->toTimestamp()->toString();

		//check
		$this->assertEquals(
			$someDate->toDialectString(ImaginaryDialect::me()),
			$expectation
		);
	}

	/**
	 * @group ff
	 */
	public function testSleeping() {
		$time = TimestampTZ::create('2011-03-08 12:12:12 PST');
		$sleepedTime = unserialize(serialize($time));

		$this->assertEquals(TimestampTZ::compare($time, $sleepedTime), 0);
	}
}
?>
