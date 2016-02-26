<?php
/***************************************************************************
 *   Copyright (C) 2008-2009 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Base
 **/
class IntervalUnit
{
    /** @var null  */
    private $name = null;

    /** @var null|integer */
    private $months = null;
    /** @var null|integer*/
    private $days = null;
    /** @var null|integer */
    private $seconds = null;

    /**
     * IntervalUnit constructor.
     * @param $name
     * @throws  WrongArgumentException|UnimplementedFeatureException
     */
    private function __construct($name)
    {
        $units = self::getUnits();

        if (!isset($units[$name])) {
            throw new WrongArgumentException(
                "know nothing about unit '$name'"
            );
        }

        if (!$units[$name]) {
            throw new UnimplementedFeatureException(
                'need for complex logic, see manual'
            );
        }

        $this->name = $name;

        $this->months = $units[$name][0];
        $this->days = $units[$name][1];
        $this->seconds = $units[$name][2];

        $notNulls = 0;

        if ($this->months > 0) {
            ++$notNulls;
        }

        if ($this->days > 0) {
            ++$notNulls;
        }

        if ($this->seconds > 0) {
            ++$notNulls;
        }

        Assert::isEqual($notNulls, 1, "broken unit '$name'");
    }

    /**
     * @return array|null
     */
    private static function getUnits()
    {
        static $result = null;

        if (!$result) {
            $result = [
                // name        => array(months, days,   seconds)
                'microsecond' => [0, 0, 0.000001],
                'millisecond' => [0, 0, 0.001],
                'second' => [0, 0, 1],
                'minute' => [0, 0, 60],
                'hour' => [0, 0, 3600],
                'day' => [0, 1, 0],
                'week' => [0, 7, 0],
                'month' => [1, 0, 0],
                'year' => [12, 0, 0],
                'decade' => [120, 0, 0],
                'century' => [],
                'millennium' => []
            ];
        }

        return $result;
    }


    /**
     * @param $id
     * @return mixed
     */
    private static function getInstance($id)
    {
        static $instances = [];

        if (!isset($instances[$id])) {
            $instances[$id] = new self($id);
        }

        return $instances[$id];
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param DateRange $range
     * @param bool $overlappedBounds
     * @return integer
     * @throws WrongArgumentException
     */
    public function countInRange(DateRange $range, $overlappedBounds = true) : int
    {
        $range = $range->toTimestampRange();

        $start = $this->truncate(
            $range->getStart(), !$overlappedBounds
        );

        $end = $this->truncate(
            $range->getEnd(), $overlappedBounds
        );

        if ($this->seconds) {

            $result =
                ($end->toStamp() - $start->toStamp())
                / $this->seconds;

        } elseif ($this->days) {

            $epochStartTruncated = new Date('1970-01-05');

            $startDifference = Date::dayDifference(
                $epochStartTruncated, new Date($start->toDate())
            );

            $endDifference = Date::dayDifference(
                $epochStartTruncated, new Date($end->toDate())
            );

            $result = ($endDifference - $startDifference) / $this->days;


        } elseif ($this->months) {

            $startMonthsCount = (int)$start->getYear() * 12 + (int)($start->getMonth() - 1);
            $endMonthsCount = (int)$end->getYear() * 12 + (int)($end->getMonth() - 1);

            $result = ($endMonthsCount - $startMonthsCount) / $this->months;
        }

        Assert::isEqual(
            $result, (int) $result,
            'floating point mistake, arguments: '
            . $this->name . ', '
            . $start->toStamp() . ', ' . $end->toStamp() . ', '
            . 'result: ' . var_export($result, true)
        );

        return (int) $result;
    }

    /**
     * Emulates PostgreSQL's date_trunc() function
     *
     * @param Date $time
     * @param bool $ceil
     * @return Timestamp
     * @throws WrongArgumentException
     */
    public function truncate(Date $time, $ceil = false) : Timestamp
    {
        $time = $time->toTimestamp();

        $function = $ceil ? 'ceil' : 'floor';

        if ($this->seconds) {

            if ($this->seconds < 1) {
                return $time->spawn();
            }

            $truncated = (int) (
                $function($time->toStamp() / $this->seconds) * $this->seconds
            );

            return new Timestamp($truncated);

        } elseif ($this->days) {

            $epochStartTruncated = new Date('1970-01-05');

            $truncatedDate = new Date($time->toDate());

            if ($ceil && $truncatedDate->toStamp() < $time->toStamp()) {
                $truncatedDate->modify('+1 day');
            }

            $difference = Date::dayDifference(
                $epochStartTruncated, $truncatedDate
            );

            $truncated = (int) (
                $function($difference / $this->days) * $this->days
            );

            return new Timestamp(
                $epochStartTruncated->spawn($truncated . ' days')->toStamp()
            );

        } elseif ($this->months) {

            $monthsCount = (int)$time->getYear() * 12 + (int)($time->getMonth() - 1);

            if (
                $ceil
                && (
                    (int)($time->getDay() - 1) + (int)$time->getHour()
                    + (int)$time->getMinute() + (int)$time->getSecond() > 0
                )
            ) {
                $monthsCount += 0.1;
            } // delta

            $truncated = (int) (
                $function($monthsCount / $this->months) *
                ($this->months)
            );

            $months = $truncated % 12;

            $years = ($truncated - $months) / 12;

            Assert::isEqual($years, (int) $years);

            $years = (int) $years;

            $months = $months + 1;

            return new Timestamp("{$years}-{$months}-01 00:00:00");
        }

        Assert::isUnreachable();
    }

    /**
     * @param IntervalUnit $unit
     * @return int|null
     */
    public function compareTo(IntervalUnit $unit)
    {
        $monthsDiffer = $this->months - $unit->months;

        if ($monthsDiffer) {
            return $monthsDiffer;
        }

        $daysDiffer = $this->days - $unit->days;

        if ($daysDiffer) {
            return $daysDiffer;
        }

        $secondsDiffer = $this->seconds - $unit->seconds;

        if ($secondsDiffer) {
            return $secondsDiffer;
        }

        return 0;
    }
}

