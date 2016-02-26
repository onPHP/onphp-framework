<?php
/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Integer's set.
 *
 * @ingroup Helpers
 **/
class IntegerSet extends Range
{

    function __construct($min, $max)
    {
        parent::__construct($min, $max);
    }

    public function contains($value)
    {
        if (
            $this->getMin() <= $value
            && $value <= $this->getMax()
        )
            return true;
        else
            return false;
    }
}

