<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Calendar day representation.
 *
 * @ingroup Calendar
 **/
class CalendarDay extends Date
{
    private $selected = null;
    private $outside = null;

    /**
     * @return CalendarDay
     **/
    public static function create($timestamp)
    {
        return new self($timestamp);
    }

    public function  __sleep()
    {
        $sleep = parent::__sleep();
        $sleep[] = 'selected';
        $sleep[] = 'outside';
        return $sleep;
    }

    public function isSelected()
    {
        return $this->selected === true;
    }

    /**
     * @return CalendarDay
     **/
    public function setSelected($selected)
    {
        $this->selected = $selected === true;

        return $this;
    }

    public function isOutside()
    {
        return $this->outside;
    }

    /**
     * @return CalendarDay
     **/
    public function setOutside($outside)
    {
        $this->outside = $outside === true;

        return $this;
    }
}
