<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Primitives
 * @ingroup Module
 **/
abstract class RangedPrimitive extends BasePrimitive
{
    /** @var null  */
    protected $min = null;
    /** @var null  */
    protected $max = null;

    /**
     * @return null
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param $min
     * @return RangedPrimitive
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return null
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param $max
     * @return RangedPrimitive
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }
}
