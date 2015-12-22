<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @see Timestamp
 * @see DateRange
 *
 * @ingroup Helpers
 **/
class TimestampRange extends DateRange
{
    /**
     * @return TimestampRange
     **/
    public static function create($start = null, $end = null)
    {
        return new self($start, $end);
    }

    public function getStartStamp() // null if start is null
    {
        if ($start = $this->getStart()) {
            return $start->toStamp();
        }

        return null;
    }

    public function getEndStamp() // null if end is null
    {
        if ($end = $this->getEnd()) {
            return $end->toStamp();
        }

        return null;
    }

    /**
     * @return TimestampRange
     **/
    public function toTimestampRange()
    {
        return $this;
    }

    protected function getObjectName()
    {
        return 'Timestamp';
    }
}
