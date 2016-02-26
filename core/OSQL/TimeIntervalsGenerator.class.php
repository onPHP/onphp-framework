<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup OSQL
 **/
class TimeIntervalsGenerator extends QueryIdentification
{
    const ITERATOR_ALIAS = 'iterator';

    /** @var null */
    private $range = null;

    /** @var null */
    private $interval = null;

    /** @var bool */
    private $overlapped = true;

    /** @var string */
    private $field = 'time';


    /**
     * @return DateRange
     **/
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @param DateRange $range
     * @return TimeIntervalsGenerator
     */
    public function setRange(DateRange $range) : TimeIntervalsGenerator
    {
        $this->range = $range;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOverlapped() : bool
    {
        return $this->overlapped;
    }

    /**
     * @param bool $overlapped
     * @return TimeIntervalsGenerator
     * @throws WrongArgumentException
     */
    public function setOverlapped(bool $overlapped = true)
    {
        Assert::isBoolean($overlapped);

        $this->overlapped = ($overlapped === true);

        return $this;
    }

    /**
     * @return string
     */
    public function getField() : string
    {
        return $this->field;
    }

    /**
     * @param $field
     * @return TimeIntervalsGenerator
     */
    public function setField($field) : TimeIntervalsGenerator
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return IntervalUnit
     **/
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param IntervalUnit $interval
     * @return TimeIntervalsGenerator
     */
    public function setInterval(IntervalUnit $interval) : TimeIntervalsGenerator
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return string
     * @throws WrongArgumentException
     * @throws WrongStateException
     */
    public function toDialectString(Dialect $dialect) : string
    {
        return $this->toSelectQuery()->toDialectString($dialect);
    }

    /**
     * @return SelectQuery
     * @throws WrongArgumentException
     * @throws WrongStateException
     */
    public function toSelectQuery() : SelectQuery
    {
        if (!$this->getRange() || !$this->getInterval()) {
            throw new WrongStateException(
                'define time range and interval units first'
            );
        }

        if (!$this->getRange()->getStart() || !$this->getRange()->getEnd()) {
            throw new WrongArgumentException(
                'cannot operate with unlimited range'
            );
        }

        $firstIntervalStart =
            $this->getInterval()->truncate(
                $this->getRange()->getStart(), !$this->overlapped
            );

        $maxIntervals =
            $this->getInterval()->countInRange(
                $this->range, $this->overlapped
            ) - 1;

        $generator = $this->getSeriesGenerator(0, $maxIntervals);

        $result = (new SelectQuery())
            ->from($generator, self::ITERATOR_ALIAS)
            ->get(
                Expression::add(
                    (new DBValue($firstIntervalStart->toString()))
                        ->castTo(
                            (new DataType(DataType::TIMESTAMP))
                                ->getName()
                        ),

                    Expression::mul(
                        (new DBValue("1 {$this->getInterval()->getName()}"))
                            ->castTo(
                                (new DataType(DataType::INTERVAL))
                                    ->getName()
                            ),

                        new DBField(self::ITERATOR_ALIAS)
                    )
                ),
                $this->field
            );

        return $result;
    }

    /**
     * @return DialectString
     *
     * FIXME: DBI-result, method works only for PostgreSQL.
     * Research how to generate series of values in MySQL and implement
     * this.
     **/
    private function getSeriesGenerator($start, $stop, $step = null)
    {
        if (!$step) {
            $result = new SQLFunction(
                'generate_series',
                (new DBValue($start))
                    ->castTo(
                        (new DataType(DataType::INTEGER))
                            ->getName()
                    ),

                (new DBValue($stop))
                    ->castTo(
                        (new DataType(DataType::INTEGER))
                            ->getName()
                    )
            );
        } else {
            $result = new SQLFunction(
                'generate_series',
                (new DBValue($start))
                    ->castTo(
                        (new DataType(DataType::INTEGER))
                            ->getName()
                    ),

                (new DBValue($stop))
                    ->castTo(
                        (new DataType(DataType::INTEGER))
                            ->getName()
                    ),

                (new DBValue($step))
                    ->castTo(
                        (new DataType(DataType::INTEGER))
                            ->getName()
                    )
            );
        }

        return $result;
    }
}
