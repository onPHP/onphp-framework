<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Integer's interval implementation and accompanying utility methods.
 *
 * @ingroup Helpers
 **/
class Range extends BaseRange
{
    public function __construct($min = null, $max = null)
    {
        if ($min !== null)
            Assert::isInteger($min);

        if ($max !== null)
            Assert::isInteger($max);

        parent::__construct($min, $max);
    }

    /**
     * @return Range
     **/
    public static function create($min = null, $max = null)
    {
        return new self($min, $max);
    }

    /**
     * @throws WrongArgumentException
     * @return Range
     **/
    public function setMin($min = null)
    {
        if ($min !== null)
            Assert::isInteger($min);
        else
            return $this;

        return parent::setMin($min);
    }

    /**
     * @throws WrongArgumentException
     * @return Range
     **/
    public function setMax($max = null)
    {
        if ($max !== null)
            Assert::isInteger($max);
        else
            return $this;

        return parent::setMax($max);
    }
}
