<?php

namespace OnPHP\Tests\Core;

use OnPHP\Core\Base\Date;
use OnPHP\Core\Base\Timestamp;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Base\DateRange;
use OnPHP\Main\Base\TimestampRange;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group core
 * @group date
 */
final class DateRangeTest extends TestCase
{
	public function testCreate()
	{
		$dateRange = DateRange::create();
		$this->assertInstanceOf(DateRange::class, $dateRange);
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());

		$date = Date::create('2007-01-01');
		$dateRange = DateRange::create($date);
		$this->assertInstanceOf(DateRange::class, $dateRange);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertEquals($date->toDate(), $dateRange->getStart()->toDate());
		$this->assertNull($dateRange->getEnd());

		$date = Date::create('2008-01-01');
		$dateRange = DateRange::create(null, $date);
		$this->assertInstanceOf(DateRange::class, $dateRange);
		$this->assertNull($dateRange->getStart());
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($date->toDate(), $dateRange->getEnd()->toDate());

		$start = Date::create('2007-01-01');
		$end = Date::create('2008-01-01');
		$dateRange = DateRange::create($start, $end);
		$this->assertInstanceOf(DateRange::class, $dateRange);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($start->toDate(), $dateRange->getStart()->toDate());
		$this->assertEquals($end->toDate(), $dateRange->getEnd()->toDate());

		$start = Date::create('2007-01-01');
		$end = Date::create('2008-01-01');
		try {
			DateRange::create($end, $start);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
	}

	public function testMerge()
	{
		try {
			DateRange::merge([DateRange::create()]);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::merge([DateRange::create(Date::create('2007-01-01'))]);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::merge([DateRange::create(null, Date::create('2007-12-31'))]);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::merge([
				DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31')),
				DateRange::create()
			]);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$output = DateRange::merge([DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))]);
		$this->assertCount(1, $output);

		$output = DateRange::merge([
			DateRange::create(Date::create('2007-01-01'), Date::create('2007-01-31')),
			DateRange::create(Date::create('2007-03-01'), Date::create('2007-03-31')),
			DateRange::create(Date::create('2007-05-01'), Date::create('2007-05-31')),
		]);
		$this->assertCount(3, $output);

		$output = DateRange::merge([
			DateRange::create(Date::create('2007-01-01'), Date::create('2007-01-31')),
			DateRange::create(Date::create('2007-02-20'), Date::create('2007-02-28')),
			DateRange::create(Date::create('2007-05-01'), Date::create('2007-05-31')),
			DateRange::create(Date::create('2007-03-01'), Date::create('2007-03-31')),
		]);
		$this->assertCount(3, $output);
		$this->assertEquals($output[0]->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertEquals($output[0]->getEnd()->toDate(), Date::create('2007-01-31')->toDate());
		$this->assertEquals($output[1]->getStart()->toDate(), Date::create('2007-02-20')->toDate());
		$this->assertEquals($output[1]->getEnd()->toDate(), Date::create('2007-03-31')->toDate());
		$this->assertEquals($output[2]->getStart()->toDate(), Date::create('2007-05-01')->toDate());
		$this->assertEquals($output[2]->getEnd()->toDate(), Date::create('2007-05-31')->toDate());

		$output = DateRange::merge([
			DateRange::create(Date::create('2007-01-01'), Date::create('2007-01-31')),
			DateRange::create(Date::create('2007-02-20'), Date::create('2007-02-28')),
			DateRange::create(Date::create('2007-04-01'), Date::create('2007-04-30')),
			DateRange::create(Date::create('2007-05-01'), Date::create('2007-05-31')),
			DateRange::create(Date::create('2007-03-01'), Date::create('2007-03-31')),
		]);
		$this->assertCount(3, $output);
		$this->assertEquals($output[0]->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertEquals($output[0]->getEnd()->toDate(), Date::create('2007-01-31')->toDate());
		$this->assertEquals($output[1]->getStart()->toDate(), Date::create('2007-02-20')->toDate());
		$this->assertEquals($output[1]->getEnd()->toDate(), Date::create('2007-03-31')->toDate());
		$this->assertEquals($output[2]->getStart()->toDate(), Date::create('2007-03-01')->toDate());
		$this->assertEquals($output[2]->getEnd()->toDate(), Date::create('2007-05-31')->toDate());
	}

	public function testCompare()
	{
		$this->assertEquals(0,
			DateRange::compare(
				DateRange::create(), DateRange::create()
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(),
				DateRange::create(Date::makeToday())
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(),
				DateRange::create(null, Date::makeToday())
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(),
				DateRange::create(Date::makeToday(), Date::create("+1 week"))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::makeToday()),
				DateRange::create()
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(null, Date::makeToday()),
				DateRange::create()
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::makeToday(), Date::create("+1 week")),
				DateRange::create()
			)
		);

		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(null, Date::create('2006-01-01')),
				DateRange::create(null, Date::create('2005-01-01'))
			)
		);
		$this->assertEquals(0,
			DateRange::compare(
				DateRange::create(null, Date::create('2006-01-01')),
				DateRange::create(null, Date::create('2006-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(null, Date::create('2006-01-01')),
				DateRange::create(null, Date::create('2007-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(null, Date::create('2006-01-01')),
				DateRange::create(Date::create('2007-01-01'))
			)
		);
		$this->assertEquals(0,
			DateRange::compare(
				DateRange::create(null, Date::create('2006-01-01')),
				DateRange::create(null, Date::create('2006-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(null, Date::create('2006-01-01')),
				DateRange::create(null, Date::create('2005-01-01'))
			)
		);

		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01')),
				DateRange::create(null, Date::create('2005-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01')),
				DateRange::create(null, Date::create('2006-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01')),
				DateRange::create(null, Date::create('2007-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01')),
				DateRange::create(Date::create('2007-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01')),
				DateRange::create(null, Date::create('2006-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01')),
				DateRange::create(null, Date::create('2005-01-01'))
			)
		);

		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(null, Date::create('2005-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(null, Date::create('2006-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(null, Date::create('2006-06-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(null, Date::create('2007-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(null, Date::create('2008-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2005-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2006-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2006-06-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2007-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2008-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2005-01-01'), Date::create('2005-06-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2005-01-01'), Date::create('2006-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2005-01-01'), Date::create('2006-06-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2005-01-01'), Date::create('2007-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2005-01-01'), Date::create('2008-01-01'))
			)
		);
		$this->assertEquals(1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2006-01-01'), Date::create('2006-06-01'))
			)
		);
		$this->assertEquals(0,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2006-01-01'), Date::create('2008-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2006-06-01'), Date::create('2006-07-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2006-06-01'), Date::create('2007-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2006-06-01'), Date::create('2008-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2007-01-01'), Date::create('2008-01-01'))
			)
		);
		$this->assertEquals(-1,
			DateRange::compare(
				DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')),
				DateRange::create(Date::create('2008-01-01'), Date::create('2009-01-01'))
			)
		);
	}

	public function testConstructor()
	{
		$dateRange = new DateRange;
		$this->assertInstanceOf(DateRange::class, $dateRange);
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());

		$date = Date::create('2007-01-01');
		$dateRange = new DateRange($date);
		$this->assertInstanceOf(DateRange::class, $dateRange);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertEquals($date->toDate(), $dateRange->getStart()->toDate());
		$this->assertNull($dateRange->getEnd());

		$date = Date::create('2008-01-01');
		$dateRange = new DateRange(null, $date);
		$this->assertInstanceOf(DateRange::class, $dateRange);
		$this->assertNull($dateRange->getStart());
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($date->toDate(), $dateRange->getEnd()->toDate());

		$start = Date::create('2007-01-01');
		$end = Date::create('2008-01-01');
		$dateRange = new DateRange($start, $end);
		$this->assertInstanceOf(DateRange::class, $dateRange);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($start->toDate(), $dateRange->getStart()->toDate());
		$this->assertEquals($end->toDate(), $dateRange->getEnd()->toDate());

		$start = Date::create('2007-01-01');
		$end = Date::create('2008-01-01');
		try {
			new DateRange($end, $start);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
	}

	public function testClone()
	{
		$dateRange = DateRange::create();
		$dateRangeClone = clone $dateRange;
		$this->assertInstanceOf(DateRange::class, $dateRangeClone);
		$this->assertNull($dateRangeClone->getStart());
		$this->assertNull($dateRangeClone->getEnd());

		$date = Date::create('2007-01-01');
		$dateRange = DateRange::create($date);
		$dateRangeClone = clone $dateRange;
		$this->assertInstanceOf(DateRange::class, $dateRangeClone);
		$this->assertInstanceOf(Date::class, $dateRangeClone->getStart());
		$this->assertEquals($date->toDate(), $dateRangeClone->getStart()->toDate());
		$this->assertNull($dateRangeClone->getEnd());
		$dateRange->dropStart();
		$this->assertInstanceOf(Date::class, $dateRangeClone->getStart());

		$date = Date::create('2008-01-01');
		$dateRange = DateRange::create(null, $date);
		$dateRangeClone = clone $dateRange;
		$this->assertInstanceOf(DateRange::class, $dateRangeClone);
		$this->assertNull($dateRangeClone->getStart());
		$this->assertInstanceOf(Date::class, $dateRangeClone->getEnd());
		$this->assertEquals($date->toDate(), $dateRangeClone->getEnd()->toDate());
		$dateRange->dropEnd();
		$this->assertInstanceOf(Date::class, $dateRangeClone->getEnd());

		$start = Date::create('2007-01-01');
		$end = Date::create('2008-01-01');
		$dateRange = DateRange::create($start, $end);
		$dateRangeClone = clone $dateRange;
		$this->assertInstanceOf(DateRange::class, $dateRangeClone);
		$this->assertInstanceOf(Date::class, $dateRangeClone->getStart());
		$this->assertInstanceOf(Date::class, $dateRangeClone->getEnd());
		$this->assertEquals($start->toDate(), $dateRangeClone->getStart()->toDate());
		$this->assertEquals($end->toDate(), $dateRangeClone->getEnd()->toDate());
		$dateRange->dropStart()->dropEnd();
		$this->assertInstanceOf(Date::class, $dateRangeClone->getStart());
		$this->assertInstanceOf(Date::class, $dateRangeClone->getEnd());
	}

	public function testSetStart()
	{
		$dateRange = DateRange::create();
		$start = Date::create('2007-01-01');
		$end = Date::create('2008-01-01');

		$dateRange->setStart($end);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertEquals($end->toDate(), $dateRange->getStart()->toDate());
		$dateRange->setStart($start);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertEquals($start->toDate(), $dateRange->getStart()->toDate());
		$dateRange->setEnd($start);
		try {
			$dateRange->setStart($end);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
	}

	public function testSafeSetStart()
	{
		$dateRange = DateRange::create();
		$start = Date::create('2007-01-01');
		$end = Date::create('2008-01-01');

		$dateRange->safeSetStart($start);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertEquals($start->toDate(), $dateRange->getStart()->toDate());

		$dateRange = DateRange::create(null, $start);
		$dateRange->safeSetStart($end);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($dateRange->getEnd()->toDate(), $dateRange->getStart()->toDate());
		$this->assertEquals($start->toDate(), $dateRange->getStart()->toDate());

		$dateRange = DateRange::create(null, $end);
		$dateRange->safeSetStart($start);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertEquals($start->toDate(), $dateRange->getStart()->toDate());
	}

	public function testSafeSetEnd()
	{
		$dateRange = DateRange::create();
		$start = Date::create('2007-01-01');
		$end = Date::create('2008-01-01');

		$dateRange->safeSetEnd($end);
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($end->toDate(), $dateRange->getEnd()->toDate());

		$dateRange = DateRange::create($end);
		$dateRange->safeSetEnd($start);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($dateRange->getEnd()->toDate(), $dateRange->getStart()->toDate());
		$this->assertEquals($end->toDate(), $dateRange->getEnd()->toDate());

		$dateRange = DateRange::create($start);
		$dateRange->safeSetEnd($end);
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($end->toDate(), $dateRange->getEnd()->toDate());
	}

	public function testSetEnd()
	{
		$dateRange = DateRange::create();
		$start = Date::create('2007-01-01');
		$end = Date::create('2008-01-01');

		$dateRange->setEnd($start);
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($start->toDate(), $dateRange->getEnd()->toDate());
		$dateRange->setEnd($end);
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($end->toDate(), $dateRange->getEnd()->toDate());
		$dateRange->setStart($end);
		try {
			$dateRange->setEnd($start);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
	}

	public function testLazySet()
	{
		$dateRange = DateRange::create()->lazySet();
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());

		$date = Date::create('2007-01-01');
		$dateRange = DateRange::create()->lazySet($date);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertEquals($date->toDate(), $dateRange->getStart()->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create()->lazySet(null, $date);
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($date->toDate(), $dateRange->getEnd()->toDate());
		$this->assertNull($dateRange->getStart());

		$start = Date::create('2007-01-01');
		$end = Date::create('2008-01-01');
		$dateRange = DateRange::create()->lazySet($start, $end);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($start->toDate(), $dateRange->getStart()->toDate());
		$this->assertEquals($end->toDate(), $dateRange->getEnd()->toDate());
		$dateRange = DateRange::create()->lazySet($end, $start);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($start->toDate(), $dateRange->getStart()->toDate());
		$this->assertEquals($end->toDate(), $dateRange->getEnd()->toDate());
	}

	public function testDropStart()
	{
		$dateRange = DateRange::create();
		$start = Date::create('2007-01-01');
		$dateRange->dropStart();

		$dateRange->setStart($start);
		$dateRange->dropStart();
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getStartStamp());
	}

	public function testDropEnd()
	{
		$dateRange = DateRange::create();
		$end = Date::create('2008-01-01');
		$dateRange->dropEnd();

		$dateRange->setEnd($end);
		$dateRange->dropEnd();
		$this->assertNull($dateRange->getEnd());
		$this->assertNull($dateRange->getEndStamp());
	}

	public function testIsEmpty()
	{
		$this->assertTrue(DateRange::create()->isEmpty());
		$this->assertFalse(DateRange::create(Date::create('2007-01-01'), null)->isEmpty());
		$this->assertFalse(DateRange::create(null, Date::create('2008-01-01'))->isEmpty());
		$this->assertFalse(
			DateRange::create(Date::create('2007-01-01'), Date::create('2008-01-01'))->isEmpty()
		);
	}

	public function testGetStart()
	{
		$dateRange = DateRange::create();
		$this->assertNull($dateRange->getStart());

		$date = Date::create('2007-01-01');
		$dateRange = DateRange::create($date);
		$this->assertInstanceOf(Date::class, $dateRange->getStart());
		$this->assertEquals($date->toDate(), $dateRange->getStart()->toDate());
		$dateRange->dropStart();
		$this->assertNull($dateRange->getStart());
	}

	public function testGetEnd()
	{
		$dateRange = DateRange::create();
		$this->assertNull($dateRange->getEnd());

		$date = Date::create('2007-12-31');
		$dateRange = DateRange::create(null, $date);
		$this->assertInstanceOf(Date::class, $dateRange->getEnd());
		$this->assertEquals($date->toDate(), $dateRange->getEnd()->toDate());
		$dateRange->dropEnd();
		$this->assertNull($dateRange->getEnd());
	}

	public function testToDateString()
	{
		$start = Date::create('2007-01-01');
		$end = Date::create('2007-12-31');
		$methodParams = [
			'internalDelimiter' => '-',
			'dateDelimiter' => ' - ',
		];

		$dateRangeReflection = new \ReflectionClass(DateRange::class);
		$toDateStringMethod = $dateRangeReflection->getMethod('toDateString');
		$this->assertCount($toDateStringMethod->getNumberOfParameters(), $methodParams);
		foreach ($toDateStringMethod->getParameters() as $param) {
			$this->assertArrayHasKey($param->getName(), $methodParams);
			$this->assertEquals($methodParams[$param->getName()], $param->getDefaultValue());
		}

		$this->assertNull(DateRange::create()->toDateString());
		$this->assertEquals(
			$start->toDate($methodParams['internalDelimiter']) . $methodParams['dateDelimiter'] . $end->toDate($methodParams['internalDelimiter']),
			DateRange::create($start, $end)->toDateString()
		);
		$this->assertEquals($start->toDate($methodParams['internalDelimiter']), DateRange::create($start)->toDateString());
		$this->assertEquals($end->toDate($methodParams['internalDelimiter']), DateRange::create(null, $end)->toDateString());
	}

	public function testToString()
	{
		$start = Date::create('2007-01-01');
		$end = Date::create('2007-12-31');
		$methodParams = [
			'delimiter' => ' - ',
		];

		$dateRangeReflection = new \ReflectionClass(DateRange::class);
		$toDateStringMethod = $dateRangeReflection->getMethod('toString');
		$this->assertCount($toDateStringMethod->getNumberOfParameters(), $methodParams);
		foreach ($toDateStringMethod->getParameters() as $param) {
			$this->assertArrayHasKey($param->getName(), $methodParams);
			$this->assertEquals($methodParams[$param->getName()], $param->getDefaultValue());
		}

		$this->assertNull(DateRange::create()->toString());
		$this->assertEquals(
			$start->toDate() . $methodParams['delimiter'] . $end->toDate(),
			DateRange::create($start, $end)->toDateString()
		);
		$this->assertEquals($start->toDate(), DateRange::create($start)->toDateString());
		$this->assertEquals($end->toDate(), DateRange::create(null, $end)->toDateString());
	}

	public function testOverlaps()
	{
		$dataRange = DateRange::create()->lazySet(Date::create('2007-01-01'), Date::create('2007-12-31'));

		$this->assertTrue($dataRange->overlaps(DateRange::create()));

		$this->assertTrue($dataRange->overlaps(DateRange::create()->setStart(Date::create('2006-12-31'))));
		$this->assertFalse(
			$dataRange
				->overlaps(
					DateRange::create()
						->setStart(Date::create('2006-12-01'))
						->setEnd(Date::create('2006-12-31'))
				)
		);
		$this->assertFalse($dataRange->overlaps(DateRange::create()->setEnd(Date::create('2006-12-31'))));

		$this->assertTrue(
			$dataRange
				->overlaps(
					DateRange::create()
						->setStart(Date::create('2006-01-01'))
						->setEnd(Date::create('2007-01-01'))
				)
		);
		$this->assertTrue(
			$dataRange
				->overlaps(
					DateRange::create()
						->setStart(Date::create('2007-12-31'))
						->setEnd(Date::create('2008-12-31'))
				)
		);
		$this->assertTrue(
			$dataRange
				->overlaps(
					DateRange::create()
						->setStart(Date::create('2007-01-01'))
						->setEnd(Date::create('2007-12-31'))
				)
		);
		$this->assertTrue($dataRange->overlaps(DateRange::create()->setStart(Date::create('2007-12-31'))));
		$this->assertTrue($dataRange->overlaps(DateRange::create()->setEnd(Date::create('2007-01-01'))));
		$this->assertTrue(
			$dataRange
				->overlaps(
					DateRange::create()
						->setStart(Date::create('2007-02-01'))
						->setEnd(Date::create('2007-03-01'))
				)
		);
		$this->assertTrue($dataRange->overlaps(DateRange::create()->setStart(Date::create('2007-06-12'))));
		$this->assertTrue($dataRange->overlaps(DateRange::create()->setEnd(Date::create('2007-06-01'))));

		$this->assertFalse($dataRange->overlaps(DateRange::create()->setStart(Date::create('2008-01-01'))));
		$this->assertFalse(
			$dataRange
				->overlaps(
					DateRange::create()
						->setStart(Date::create('2008-01-01'))
						->setEnd(Date::create('2008-12-31'))
				)
		);
		$this->assertTrue(
			$dataRange
				->overlaps(
					DateRange::create()
						->setEnd(Date::create('2006-12-31'))
						->setEnd(Date::create('2008-01-01'))
				)
		);
		$this->assertTrue(
			$dataRange->overlaps(
				DateRange::create()->lazySet(Date::create('2007-05-14'), Date::create('2008-03-29'))
			)
		);
		$this->assertTrue(
			$dataRange->overlaps(
				DateRange::create()->lazySet(Date::create('2006-05-14'), Date::create('2007-03-29'))
			)
		);

		$this->assertTrue(DateRange::create()->overlaps(DateRange::create()));
		$this->assertTrue(
			DateRange::create()
				->overlaps(
					DateRange::create()->lazySet(Date::create('2007-03-28'), Date::create('2008-03-27'))
				)
		);
	}

	public function testContains()
	{
		$this->assertTrue(DateRange::create()->contains(Date::create('2007-06-01')));

		$dataRange = DateRange::create()->lazySet(Date::create('2007-01-01'), Date::create('2007-12-31'));
		$this->assertTrue($dataRange->contains(Date::create('2007-06-01')));
		$this->assertTrue($dataRange->contains(Date::create('2007-01-01')));
		$this->assertTrue($dataRange->contains(Date::create('2007-12-31')));
		$this->assertFalse($dataRange->contains(Date::create('2006-06-01')));
		$this->assertFalse($dataRange->contains(Date::create('2008-06-01')));

		$dataRange = DateRange::create()->setStart(Date::create('2007-01-01'));
		$this->assertTrue($dataRange->contains(Date::create('2007-06-01')));
		$this->assertTrue($dataRange->contains(Date::create('2007-01-01')));
		$this->assertTrue($dataRange->contains(Date::create('2007-12-31')));
		$this->assertFalse($dataRange->contains(Date::create('2006-06-01')));

		$dataRange = DateRange::create()->setEnd(Date::create('2007-12-31'));
		$this->assertTrue($dataRange->contains(Date::create('2007-06-01')));
		$this->assertTrue($dataRange->contains(Date::create('2007-12-31')));
		$this->assertFalse($dataRange->contains(Date::create('2008-06-01')));
	}

	public function testSplit()
	{
		$start = new Date('2007-03-26');
		$end = new Date('2007-04-15');

		$range = DateRange::create()->lazySet($start, $end);

		$dates = $range->split();

		$this->assertCount(21, $dates);

		$this->assertEquals(reset($dates), $start);
		$this->assertEquals(end($dates), $end);

		try {
			DateRange::create()->split();
			$this->fail('expected WrongArgumentException exception');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::create()->setStart(Date::create('2007-01-01'))->split();
			$this->fail('expected WrongArgumentException exception');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::create()->setEnd(Date::create('2006-12-31'))->split();
			$this->fail('expected WrongArgumentException exception');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$start = $end = Date::create('2006-01-01');
		$ranges = DateRange::create($start, $end)->split();
		$this->assertCount(1, $ranges);
		$rangeDate = current($ranges);
		$this->assertEquals($rangeDate, $start);
		$this->assertEquals($rangeDate, $end);
	}

	public function testIsNeighbour()
	{
		$dateRangeEmpty = DateRange::create();
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'));

		try {
			$dateRangeEmpty->isNeighbour($dateRangeEmpty);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		try {
			$dateRangeEmpty->isNeighbour($dateRange);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			$dateRangeEmpty->isNeighbour(DateRange::create()->setStart(Date::create('2007-01-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			$dateRangeEmpty->isNeighbour(DateRange::create()->setEnd(Date::create('2007-12-31')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		try {
			$dateRange->isNeighbour($dateRangeEmpty);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			$dateRange->isNeighbour(DateRange::create()->setStart(Date::create('2007-01-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			$dateRange->isNeighbour(DateRange::create()->setEnd(Date::create('2007-12-31')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		try {
			DateRange::create()
				->setStart(Date::create('2007-01-01'))
				->isNeighbour($dateRange);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::create()
				->setStart(Date::create('2007-01-01'))
				->isNeighbour($dateRangeEmpty);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::create()
				->setStart(Date::create('2007-01-01'))
				->isNeighbour(DateRange::create()->setStart(Date::create('2007-01-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::create()
				->setStart(Date::create('2007-01-01'))
				->isNeighbour(DateRange::create()->setEnd(Date::create('2007-12-31')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		try {
			DateRange::create()
				->setEnd(Date::create('2007-12-31'))
				->isNeighbour($dateRange);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::create()
				->setEnd(Date::create('2007-12-31'))
				->isNeighbour($dateRangeEmpty);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::create()
				->setEnd(Date::create('2007-12-31'))
				->isNeighbour(DateRange::create()->setStart(Date::create('2007-01-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			DateRange::create()
				->setEnd(Date::create('2007-12-31'))
				->isNeighbour(DateRange::create()->setEnd(Date::create('2007-12-31')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$this->assertFalse(
			$dateRange
				->isNeighbour(DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-01')))
		);
		$this->assertFalse(
			$dateRange
				->isNeighbour(DateRange::create(Date::create('2008-02-01'), Date::create('2008-12-31')))
		);
		$this->assertTrue(
			$dateRange
				->isNeighbour(DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31')))
		);
		$this->assertTrue(
			$dateRange
				->isNeighbour(DateRange::create(Date::create('2008-01-01'), Date::create('2008-12-31')))
		);
		$this->assertTrue(
			$dateRange
				->isNeighbour(DateRange::create(Date::create('2007-12-31'), Date::create('2008-12-31')))
		);
		$this->assertTrue(
			$dateRange
				->isNeighbour(DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31')))
		);
		$this->assertTrue(
			$dateRange
				->isNeighbour(DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')))
		);
		$this->assertTrue(
			$dateRange
				->isNeighbour(DateRange::create(Date::create('2006-01-01'), Date::create('2007-06-01')))
		);
		$this->assertTrue(
			$dateRange
				->isNeighbour(DateRange::create(Date::create('2007-06-01'), Date::create('2008-06-01')))
		);
		$this->assertTrue(
			$dateRange
				->isNeighbour(DateRange::create(Date::create('2007-06-01'), Date::create('2007-07-01')))
		);
	}

	public function testIsOpen()
	{
		$this->assertTrue(DateRange::create()->isOpen());
		$this->assertTrue(DateRange::create(Date::create('2007-06-01'))->isOpen());
		$this->assertTrue(DateRange::create()->setEnd(Date::create('2007-06-01'))->isOpen());
		$this->assertFalse(DateRange::create(Date::create('2007-06-01'), Date::create('2007-06-01'))->isOpen());
	}

	public function testEnlarge()
	{
		$dateRange = DateRange::create()->enlarge(DateRange::create());
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());

		$dateRange = DateRange::create()->enlarge(DateRange::create(Date::create('2007-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create()->enlarge(DateRange::create(null, Date::create('2007-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create()->enlarge(DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());

		$dateRange = DateRange::create(Date::create('2007-01-01'))->enlarge(DateRange::create());
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2006-01-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2006-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2007-01-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2008-01-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(null, Date::create('2006-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(null, Date::create('2007-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(null, Date::create('2008-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2006-01-01'), Date::create('2006-06-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2006-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2006-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2006-01-01'), Date::create('2008-01-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2006-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2007-01-01'), Date::create('2008-01-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2008-01-01'), Date::create('2008-06-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());

		$dateRange = DateRange::create(null, Date::create('2007-01-01'))->enlarge(DateRange::create());
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2006-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2007-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2008-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(null, Date::create('2006-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-01-01')->toDate());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(null, Date::create('2007-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-01-01')->toDate());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(null, Date::create('2008-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2008-01-01')->toDate());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2006-01-01'), Date::create('2006-06-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-01-01')->toDate());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-01-01')->toDate());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2006-01-01'), Date::create('2008-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2008-01-01')->toDate());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2007-01-01'), Date::create('2008-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2008-01-01')->toDate());
		$dateRange = DateRange::create(null, Date::create('2007-01-01'))
			->enlarge(DateRange::create(Date::create('2008-01-01'), Date::create('2008-06-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2008-06-01')->toDate());

		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create());
		$this->assertNull($dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2006-01-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2006-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2007-01-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2007-06-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2007-12-31')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2008-01-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertNull($dateRange->getEnd());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(null, Date::create('2006-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(null, Date::create('2007-01-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(null, Date::create('2007-06-01')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(null, Date::create('2007-12-31')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(null, Date::create('2008-12-31')));
		$this->assertNull($dateRange->getStart());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2008-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2006-01-01'), Date::create('2006-06-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2006-01-01')->toDate());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2006-01-01'), Date::create('2007-01-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2006-01-01')->toDate());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2006-01-01'), Date::create('2007-06-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2006-01-01')->toDate());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2007-01-01'), Date::create('2007-06-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2007-02-01'), Date::create('2007-10-01')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2007-02-01'), Date::create('2007-12-31')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2007-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2007-02-01'), Date::create('2008-12-31')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2008-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2007-12-31'), Date::create('2008-12-31')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2008-12-31')->toDate());
		$dateRange = DateRange::create(Date::create('2007-01-01'), Date::create('2007-12-31'))
			->enlarge(DateRange::create(Date::create('2008-06-01'), Date::create('2008-12-31')));
		$this->assertEquals($dateRange->getStart()->toDate(), Date::create('2007-01-01')->toDate());
		$this->assertEquals($dateRange->getEnd()->toDate(), Date::create('2008-12-31')->toDate());
	}

	public function testClip()
	{
		$clip = DateRange::create()->clip(DateRange::create());
		$this->assertInstanceOf(DateRange::class, $clip);
		$this->assertNull($clip->getStart());
		$this->assertNull($clip->getEnd());

		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->clip(DateRange::create());
		$this->assertNull($clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->clip(DateRange::create(null, Date::create('2005-01-01')));
		$this->assertNull($clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2005-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->clip(DateRange::create(null, Date::create('2006-01-01')));
		$this->assertNull($clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->clip(DateRange::create(null, Date::create('2007-01-01')));
		$this->assertNull($clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2005-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2005-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(null, Date::create('2006-01-01'))
				->clip(DateRange::create(Date::create('2007-01-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2005-01-01'), Date::create('2005-02-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2005-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2005-02-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2005-06-01'), Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2005-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2005-06-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2005-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2006-01-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(null, Date::create('2006-01-01'))
				->clip(DateRange::create(Date::create('2006-05-01'), Date::create('2006-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$clip = DateRange::create(Date::create('2006-01-01'))
			->clip(DateRange::create());
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertNull($clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'))
				->clip(DateRange::create(null, Date::create('2005-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(Date::create('2006-01-01'))
			->clip(DateRange::create(null, Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->clip(DateRange::create(null, Date::create('2007-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2007-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2005-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertNull($clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertNull($clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2007-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertNull($clip->getEnd());
		$this->assertEquals(Date::create('2007-01-01')->toDate(), $clip->getStart()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'))
				->clip(DateRange::create(Date::create('2005-01-01'), Date::create('2005-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2005-01-01'), Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2005-06-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2006-01-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->clip(DateRange::create(Date::create('2007-01-01'), Date::create('2007-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2007-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2007-06-01')->toDate(), $clip->getEnd()->toDate());

		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create());
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
				->clip(DateRange::create(null, Date::create('2005-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(null, Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(null, Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(null, Date::create('2006-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(null, Date::create('2007-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2005-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2006-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
				->clip(DateRange::create(Date::create('2007-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2005-01-01'), Date::create('2007-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
				->clip(DateRange::create(Date::create('2005-01-01'), Date::create('2005-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2005-01-01'), Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2005-01-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2005-01-01'), Date::create('2006-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2006-01-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2006-01-01'), Date::create('2007-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2006-06-01'), Date::create('2006-07-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-07-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2006-06-01'), Date::create('2006-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2006-06-01'), Date::create('2007-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->clip(DateRange::create(Date::create('2006-12-31'), Date::create('2007-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
				->clip(DateRange::create(Date::create('2007-06-01'), Date::create('2007-12-31')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
	}

	public function testLightCopyOnClip()
	{
		$clip = DateRange::create()->lightCopyOnClip(DateRange::create());
		$this->assertInstanceOf(DateRange::class, $clip);
		$this->assertNull($clip->getStart());
		$this->assertNull($clip->getEnd());

		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create());
		$this->assertNull($clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(null, Date::create('2005-01-01')));
		$this->assertNull($clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2005-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(null, Date::create('2006-01-01')));
		$this->assertNull($clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(null, Date::create('2007-01-01')));
		$this->assertNull($clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2005-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		try {
			$clip = DateRange::create(null, Date::create('2006-01-01'))
				->lightCopyOnClip(DateRange::create(Date::create('2007-01-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-01-01'), Date::create('2005-02-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2005-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2005-02-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-06-01'), Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2005-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-06-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2005-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(null, Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-01-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(null, Date::create('2006-01-01'))
				->lightCopyOnClip(DateRange::create(Date::create('2006-05-01'), Date::create('2006-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$clip = DateRange::create(Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create());
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertNull($clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'))
				->lightCopyOnClip(DateRange::create(null, Date::create('2005-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(null, Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(null, Date::create('2007-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2007-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertNull($clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertNull($clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2007-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertNull($clip->getEnd());
		$this->assertEquals(Date::create('2007-01-01')->toDate(), $clip->getStart()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'))
				->lightCopyOnClip(DateRange::create(Date::create('2005-01-01'), Date::create('2005-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-01-01'), Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-06-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-01-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'))
			->lightCopyOnClip(DateRange::create(Date::create('2007-01-01'), Date::create('2007-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2007-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2007-06-01')->toDate(), $clip->getEnd()->toDate());

		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create());
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
				->lightCopyOnClip(DateRange::create(null, Date::create('2005-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(null, Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(null, Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(null, Date::create('2006-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(null, Date::create('2007-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
				->lightCopyOnClip(DateRange::create(Date::create('2007-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-01-01'), Date::create('2007-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
				->lightCopyOnClip(DateRange::create(Date::create('2005-01-01'), Date::create('2005-06-01')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-01-01'), Date::create('2006-01-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-01-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2005-01-01'), Date::create('2006-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-01-01'), Date::create('2006-06-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-01-01'), Date::create('2007-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-01-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-06-01'), Date::create('2006-07-01')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-07-01')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-06-01'), Date::create('2006-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-06-01'), Date::create('2007-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-06-01')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		$clip = DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
			->lightCopyOnClip(DateRange::create(Date::create('2006-12-31'), Date::create('2007-12-31')));
		$this->assertInstanceOf(Date::class, $clip->getStart());
		$this->assertInstanceOf(Date::class, $clip->getEnd());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getStart()->toDate());
		$this->assertEquals(Date::create('2006-12-31')->toDate(), $clip->getEnd()->toDate());
		try {
			DateRange::create(Date::create('2006-01-01'), Date::create('2006-12-31'))
				->lightCopyOnClip(DateRange::create(Date::create('2007-06-01'), Date::create('2007-12-31')));
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
	}

	public function testGetStartStamp()
	{
		$date = Date::makeToday();
		$dateNew = $date->spawn("+1 week");
		$dateRange = DateRange::create();
		$this->assertNull($dateRange->getStartStamp());

		$dateRange->setStart($date);
		$this->assertIsInt($dateRange->getStartStamp());
		$this->assertEquals($date->getDayStartStamp(), $dateRange->getStartStamp());
		$dateRange->setStart($dateNew);
		$this->assertIsInt($dateRange->getStartStamp());
		$this->assertEquals($dateNew->getDayStartStamp(), $dateRange->getStartStamp());
		$dateRange->dropStart();
		$this->assertNull($dateRange->getStartStamp());
	}

	public function testGetEndStamp()
	{
		$date = Date::makeToday();
		$dateNew = $date->spawn("+1 week");
		$dateRange = DateRange::create();
		$this->assertNull($dateRange->getEndStamp());

		$dateRange->setEnd($date);
		$this->assertIsInt($dateRange->getEndStamp());
		$this->assertEquals($date->getDayEndStamp(), $dateRange->getEndStamp());
		$dateRange->setEnd($dateNew);
		$this->assertIsInt($dateRange->getEndStamp());
		$this->assertEquals($dateNew->getDayEndStamp(), $dateRange->getEndStamp());
		$dateRange->dropEnd();
		$this->assertNull($dateRange->getEndStamp());
	}

	public function testIsOneDay()
	{
		$this->assertFalse(DateRange::create()->isOneDay());
		$this->assertFalse(DateRange::create(Date::makeToday())->isOneDay());
		$this->assertFalse(DateRange::create(null, Date::makeToday())->isOneDay());
		$this->assertFalse(DateRange::create(Date::makeToday(), Date::create("+1 week"))->isOneDay());
		$this->assertTrue(DateRange::create(Date::create("+1 week"), Date::create("+1 week"))->isOneDay());
	}

	public function testToTimestampRange()
	{
		$tsRange = DateRange::create()->toTimestampRange();
		$this->assertInstanceOf(TimestampRange::class, $tsRange);
		$this->assertNull($tsRange->getStart());
		$this->assertNull($tsRange->getEnd());

		$start = Date::makeToday();
		$end = $start->spawn("+1 week");
		$tsRange = DateRange::create($start)->toTimestampRange();
		$this->assertInstanceOf(Timestamp::class, $tsRange->getStart());
		$this->assertNull($tsRange->getEnd());
		$this->assertEquals($start->getDayStartStamp(), $tsRange->getStartStamp());

		$tsRange = DateRange::create($start, $end)->toTimestampRange();
		$this->assertInstanceOf(Timestamp::class, $tsRange->getStart());
		$this->assertInstanceOf(Timestamp::class, $tsRange->getEnd());
		$this->assertEquals($start->getDayStartStamp(), $tsRange->getStartStamp());
		/** use day start stamp because Date to Timestamp - result time 00:00:00 */
		$this->assertEquals($end->getDayStartStamp(), $tsRange->getEndStamp());

		$tsRange = DateRange::create(null, $end)->toTimestampRange();
		$this->assertNull($tsRange->getStart());
		$this->assertInstanceOf(Timestamp::class, $tsRange->getEnd());
		/** use day start stamp because Date to Timestamp - result time 00:00:00 */
		$this->assertEquals($end->getDayStartStamp(), $tsRange->getEndStamp());
	}

	public function testCheckType()
	{
		$dateRange = new \OnPHP\Tests\TestEnvironment\Main\Base\DateRange();
		$dateRange->checkType(Date::class);
		$dateRange->checkType(Date::makeToday());
		try {
			$dateRange->checkType(DateRange::class);
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
	}

	public function testGetObjectName()
	{
		$dateRange = new \OnPHP\Tests\TestEnvironment\Main\Base\DateRange();
		$this->assertEquals($dateRange->getObjectName(), Date::class);
	}
}