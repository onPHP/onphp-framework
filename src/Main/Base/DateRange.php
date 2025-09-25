<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Base;

use OnPHP\Core\Base\Stringable;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Base\Timestamp;
use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\Date;
use OnPHP\Main\Util\ClassUtils;

/**
 * Date's interval implementation and accompanying utility methods.
 * 
 * @see Date
 * @see TimestampRange
 * @ingroup Helpers
**/
class DateRange implements Stringable, SingleRange
{
	/**
	 * @var Date|null
	 */
	private ?Date $start	= null;
	/**
	 * @var Date|null
	 */
	private ?Date $end      = null;
	/**
	 * @var int|null
	 */
	private ?int $dayStartStamp	= null;
	/**
	 * @var int|null
	 */
	private ?int $dayEndStamp	= null;

	/**
	 * @param Date|null $start
	 * @param Date|null $end
	 * @return static
	 * @throws WrongArgumentException
	 */
	public static function create(Date $start = null, Date $end = null): DateRange
	{
		return new static($start, $end);
	}

	/**
	 * @param DateRange[] $array
	 * @return DateRange[]
	 * @throws WrongArgumentException
	 */
	public static function merge(array $array): array
	{
		array_map(function ($item) {
			Assert::isInstance($item, DateRange::class);
			Assert::isTrue(!$item->isOpen());
		}, $array);

		$out = array();

		foreach ($array as $range) {
			$accepted = false;

			foreach ($out as $outRange) {
				if ($outRange->isNeighbour($range)) {
					$outRange->enlarge($range);
					$accepted = true;
				}
			}

			if (!$accepted) {
				$out[] = clone $range;
			}
		}

		return $out;
	}

	/**
	 * @param DateRange $left
	 * @param DateRange $right
	 * @return int
	 */
	public static function compare(DateRange $left, DateRange $right): int
	{
		if ($left->isEmpty() && $right->isEmpty()) {
			return 0;
		} elseif ($left->isEmpty()) {
			return 1;
		} elseif ($right->isEmpty()) {
			return -1;
		}

		$leftStart = $left->getStartStamp();
		$leftEnd = $left->getEndStamp();

		$rightStart = $right->getStartStamp();
		$rightEnd = $right->getEndStamp();

		if (
			!$leftStart && !$rightStart
			|| $leftStart && $rightStart && ($leftStart == $rightStart)
		) {
			if (
				!$leftEnd && !$rightEnd
				|| $leftEnd && $rightEnd && ($leftEnd == $rightEnd)
			) {
				return 0;
			} elseif (
				$leftEnd && !$rightEnd
				|| $leftEnd < $rightEnd
			) {
				return -1;
			}

			return 1;
		} elseif (
			!$leftStart && $rightStart
			|| $leftStart < $rightStart
		) {
			return -1;
		} elseif ($leftStart && !$rightStart) {
			return 1;
		}

		return 1;
	}

	/**
	 * @param Date|null $start
	 * @param Date|null $end
	 * @throws WrongArgumentException
	 */
	public function __construct(Date $start = null, Date $end = null)
	{
		if ($start instanceof Date) {
			$this->setStart($start);
		}

		if ($end instanceof Date) {
			$this->setEnd($end);
		}
	}

	public function __clone()
	{
		if ($this->start) {
			$this->start = clone $this->start;
		}

		if ($this->end) {
			$this->end = clone $this->end;
		}
	}

	/**
	 * @param Date $start
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function setStart(Date $start): DateRange
	{
		Assert::isFalse(
		$this->end instanceof Date && $this->end->toStamp() < $start->toStamp(),
		'start must be lower than end'
		);

		$this->start = $start;
		$this->dayStartStamp = null;

		return $this;
	}

	/**
	 * @param Date $start
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function safeSetStart(Date $start): DateRange
	{
		if (!$this->getEnd() instanceof Date) {
			$this->setStart($start);
			return $this;
		}

		$this->setStart(
			Timestamp::compare($start, $this->getEnd()) < 0
				? $start
				: $this->getEnd()
		);

		return $this;
	}

	/**
	 * @param Date $end
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function safeSetEnd(Date $end): DateRange
	{
		if (!$this->getStart() instanceof Date) {
			$this->setEnd($end);
			return $this;
		}

		$this->setEnd(
			Timestamp::compare($end, $this->getStart()) > 0
				? $end
				: $this->getStart()
		);

		return $this;
	}

	/**
	 * @param Date $end
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function setEnd(Date $end): DateRange
	{
		Assert::isFalse(
			$this->start instanceof Date && $this->start->toStamp() > $end->toStamp(),
			'end must be higher than start'
		);

		$this->end = $end;
		$this->dayEndStamp = null;

		return $this;
	}

	/**
	 * @param Date|null $start
	 * @param Date|null $end
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function lazySet(Date $start = null, Date $end = null): DateRange
	{
		if (
			$start instanceof Date
			&& $end instanceof Date
		) {
			if ($start->toStamp() >= $end->toStamp()) {
				$this->setEnd($start)->setStart($end);
			} else {
				$this->setStart($start)->setEnd($end);
			}

			return $this;
		}

		if ($start instanceof Date) {
			$this->setStart($start);
		}

		if ($end instanceof Date) {
			$this->setEnd($end);
		}

		return $this;
	}

	/**
	 * @return static
	 */
	public function dropStart(): DateRange
	{
		$this->start = null;
		$this->dayStartStamp = null;

		return $this;
	}

	/**
	 * @return static
	 */
	public function dropEnd(): DateRange
	{
		$this->end = null;
		$this->dayEndStamp = null;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return
			($this->start === null)
			&& ($this->end === null);
	}

	/**
	 * @return Date|null
	 */
	public function getStart(): ?Date
	{
		return $this->start;
	}

	/**
	 * @return Date|null
	 */
	public function getEnd(): ?Date
	{
		return $this->end;
	}

	/**
	 * @param string $internalDelimiter
	 * @param string $dateDelimiter
	 * @return string|null
	 */
	public function toDateString(string $internalDelimiter = '-', string $dateDelimiter = ' - '): ?string
	{
		if (
			$this->start instanceof Date
			&& $this->end instanceof Date
		) {
			return $this->start->toDate($internalDelimiter)
				. $dateDelimiter
				. $this->end->toDate($internalDelimiter);
		}

		if ($this->start instanceof Date) {
			return $this->start->toDate($internalDelimiter);
		}

		if ($this->end instanceof Date) {
			return $this->end->toDate($internalDelimiter);
		}

		return null;
	}

	/**
	 * @param string $delimiter
	 * @return string|null
	 */
	public function toString(string $delimiter = ' - '): ?string
	{
		if (
			$this->start instanceof Date
			&& $this->end instanceof Date
		) {
			return
				$this->start->toString()
				. $delimiter
				. $this->end->toString();
		}

		if ($this->start instanceof Date) {
			return $this->start->toString();
		}

		if ($this->end instanceof Date) {
			return $this->end->toString();
		}

		return null;
	}

	/**
	 * @param DateRange $range
	 * @return bool
	 */
	public function overlaps(DateRange $range): bool
	{
		if ($this->isEmpty() || $range->isEmpty())
			return true;

		$left = $this->getStartStamp();
		$right = $this->getEndStamp();
		$min = $range->getStartStamp();
		$max = $range->getEndStamp();

		return
			(
				$min && $max && (
					( $left && $right && (
							(($left <= $min) && ($min <= $right))
							|| (($min <= $left) && ($left <= $max))
						)
					)
					|| ( !$left && ($min <= $right) ) || ( !$right && ($left <= $max) )
				)
			)
			|| ( $min && !$max && ( !$right || ($min <= $right) ) )
			|| ( !$min && $max && ( !$left || ($left <= $max) ) )
		;
	}

	/**
	 * @param Date $date
	 * @return bool
	 * @throws WrongArgumentException
	 */
	public function contains(object $date): bool
	{
		$this->checkType($date);

		$start = $this->getStartStamp();
		$end = $this->getEndStamp();
		$probe = $date->toStamp();

		return
			(null === $start && null === $end)
			|| (null === $start && $end >= $probe)
			|| (null === $end && $start <= $probe)
			|| ($start <= $probe && $end >= $probe)
		;
	}

	/**
	 * @return Date[]
	 * @throws WrongArgumentException
	 */
	public function split(): array
	{
		Assert::isFalse($this->isOpen(), "can't split open range");

		$dates = array();
		$start = new Date($this->start->getDayStartStamp());
		$endStamp = $this->end->getDayEndStamp();

		for (
			$current = $start;
			$current->toStamp() < $endStamp;
			$current->modify('+1 day')
		) {
			$dates[] = new Date($current->getDayStartStamp());
		}

		return $dates;
	}

	/**
	 * @param DateRange $range
	 * @return bool
	 * @throws WrongArgumentException
	 */
	public function isNeighbour(DateRange $range): bool
	{
		Assert::isTrue(!$this->isOpen() && !$range->isOpen());

		return
			$this->overlaps($range)
			|| (
				$this->start->spawn('-1 day')->getDayStartStamp()
				== $range->end->getDayStartStamp()
			) || (
				$this->end->spawn('+1 day')->getDayStartStamp()
				== $range->start->getDayStartStamp()
			)
		;
	}

	/**
	 * @return bool
	 */
	public function isOpen(): bool
	{
		return
			!$this->start instanceof Date
			|| !$this->end instanceof Date;
	}

	/**
	 * enlarges $this by given $range, if last one is wider
	 * NB! Be careful if DateRange 01 Mar 2000 - 31 Mar 2000
	 * enlarge with DateRange 01 Jan 2000 - 31 Jan 2000 new
	 * DateRange will be 01 Jan 2000 - 31 Mar 2000. Will be
	 * included center range 01 Feb 2000 - 28 Feb 2000
	 * @param DateRange $range
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function enlarge(DateRange $range): DateRange
	{
		if (!$range->start instanceof Date) {
			$this->dropStart();
		} elseif (
			$this->start instanceof Date
			&& $this->start->toStamp() > $range->start->toStamp()
		) {
			$this->setStart(clone $range->start);
		}

		if (!$range->end instanceof Date) {
			$this->dropEnd();
		} elseif (
			$this->end instanceof Date
			&& $this->end->toStamp() < $range->end->toStamp()
		) {
			$this->setEnd(clone $range->end);
		}

		return $this;
	}

	/**
	 * intersection of $this and given $range
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function clip(DateRange $range): DateRange
	{
		Assert::isTrue($this->overlaps($range));

		if (
			$range->start instanceof Date
			&& (
				!$this->start instanceof Date
				|| $range->start->toStamp() > $this->start->toStamp()
			)
		) {
			$this->start = clone $range->start;
		}

		if (
			$range->end instanceof Date
			&& (
				!$this->end instanceof Date
				|| $range->end->toStamp() < $this->end->toStamp()
			)
		) {
			$this->end = clone $range->end;
		}

		return $this;
	}

	/**
	 * @param DateRange $range
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function lightCopyOnClip(DateRange $range): DateRange
	{
		return (clone $this)->clip($range);
	}

	/**
	 * @return int|null
	 */
	public function getStartStamp(): ?int
	{
		if ($this->start) {
			if (!$this->dayStartStamp) {
				$this->dayStartStamp = $this->start->getDayStartStamp();
			}

			return $this->dayStartStamp;
		}

		return null;
	}

	/**
	 * @return int|null
	 */
	public function getEndStamp(): ?int
	{
		if ($this->end) {
			if (!$this->dayEndStamp) {
				$this->dayEndStamp = $this->end->getDayEndStamp();
			}

			return $this->dayEndStamp;
		}

		return null;
	}

	/**
	 * @return bool
	 */
	public function isOneDay(): bool
	{
		return
			!$this->isOpen()
			&& $this->start->toDate() == $this->end->toDate();
	}

	/**
	 * @return TimestampRange
	 * @throws WrongArgumentException
	 */
	public function toTimestampRange(): TimestampRange
	{
		return
			TimestampRange::create(
				$this->getStart() ? $this->getStart()->toTimestamp() : null,
				$this->getEnd() ? $this->getEnd()->toTimestamp() : null
			);
	}

	/**
	 * @param $value
	 * @throws WrongArgumentException
	 */
	protected function checkType($value): void
	{
		Assert::isTrue(
			ClassUtils::isInstanceOf($value, $this->getObjectName())
		);
	}

	/**
	 * @return string
	 */
	protected function getObjectName(): string
	{
		return Date::class;
	}
}