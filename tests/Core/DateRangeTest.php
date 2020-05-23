<?php

namespace OnPHP\Tests\Core;

use OnPHP\Core\Base\Date;
use OnPHP\Main\Base\DateRange;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group core
 * @group date
 */
final class DateRangeTest extends TestCase
{
	public function testSplit()
	{
		$start = new Date('2007-03-26');
		$end = new Date('2007-04-15');

		$range = DateRange::create()->lazySet($start, $end);

		$dates = $range->split();

		$this->assertEquals(count($dates), 21);

		$this->assertEquals(reset($dates), $start);
		$this->assertEquals(end($dates), $end);
	}

	public function testOverlaps()
	{
		$this->assertTrue(
			DateRange::create()->lazySet(
				Date::create('2007-03-28'),
				Date::create('2008-03-27')
			)->
			overlaps(
				DateRange::create()->lazySet(
					Date::create('2007-05-14'),
					Date::create('2008-03-29')
				)
			)
		);

		$this->assertFalse(
			DateRange::create()->lazySet(
				Date::create('2007-03-28'),
				Date::create('2008-03-27')
			)->
			overlaps(
				DateRange::create()->lazySet(
					Date::create('2005-05-14'),
					Date::create('2006-03-29')
				)
			)
		);
	}
}
?>