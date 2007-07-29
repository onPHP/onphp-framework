<?php
	/* $Id$ */
	
	final class TimestampTest extends UnitTestCase
	{
		public function testNonEpoch()
		{
			$future = '4683-03-04';
			$after = new Timestamp($future);
			
			$this->assertEqual($after->getDay(), '04');
			$this->assertEqual($after->getMonth(), '03');
			$this->assertEqual($after->getYear(), '4683');
			
			$past = '1234-04-03';
			$before = new Timestamp($past);
			
			$this->assertEqual($before->getDay(), '03');
			$this->assertEqual($before->getMonth(), '04');
			$this->assertEqual($before->getYear(), '1234');
			
			$this->assertFalse($after->equals($before));

			$this->assertEqual($future, $after->toDate());
			$this->assertEqual($past, $before->toDate());
			
			$time = ' 00:00.00';
			$this->assertEqual($future.$time, $after->toDateTime());
			$this->assertEqual($past.$time, $before->toDateTime());
			
			try {
				new Timestamp('2007-0-0');
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
		}
	}
?>