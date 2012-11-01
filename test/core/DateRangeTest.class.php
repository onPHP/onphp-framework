<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class DateRangeTest extends TestCase
	{
		public function testSplit()
		{
			$start = new \Onphp\Date('2007-03-26');
			$end = new \Onphp\Date('2007-04-15');
			
			$range = \Onphp\DateRange::create()->lazySet($start, $end);
			
			$dates = $range->split();
			
			$this->assertEquals(count($dates), 21);
			
			$this->assertEquals(reset($dates), $start);
			$this->assertEquals(end($dates), $end);
		}
		
		public function testOverlaps()
		{
			$this->assertTrue(
				\Onphp\DateRange::create()->lazySet(
					\Onphp\Date::create('2007-03-28'),
					\Onphp\Date::create('2008-03-27')
				)->
				overlaps(
					\Onphp\DateRange::create()->lazySet(
						\Onphp\Date::create('2007-05-14'),
						\Onphp\Date::create('2008-03-29')
					)
				)
			);
			
			$this->assertFalse(
				\Onphp\DateRange::create()->lazySet(
					\Onphp\Date::create('2007-03-28'),
					\Onphp\Date::create('2008-03-27')
				)->
				overlaps(
					\Onphp\DateRange::create()->lazySet(
						\Onphp\Date::create('2005-05-14'),
						\Onphp\Date::create('2006-03-29')
					)
				)
			);
		}
	}
?>