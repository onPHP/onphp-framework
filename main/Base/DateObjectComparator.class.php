<?php

/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class DateObjectComparator extends Singleton
    implements Comparator, Instantiatable
{
    public static function me()
    {
        return Singleton::getInstance(__CLASS__);
    }

    public function compare(/*Date*/
        $one,/*Date*/
        $two)
    {
        Assert::isInstance($one, 'Date');
        Assert::isInstance($two, 'Date');

        $stamp1 = $one->toStamp();
        $stamp2 = $two->toStamp();

        if ($stamp1 == $stamp2)
            return 0;

        return ($stamp1 < $stamp2) ? -1 : 1;
    }
}