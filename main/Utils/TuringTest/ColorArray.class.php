<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Turing
 **/
class ColorArray
{
    private $colors = array();

    /**
     * @return ColorArray
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @return ColorArray
     **/
    public function add(Color $color)
    {
        $this->colors[] = $color;

        return $this;
    }

    /**
     * @return ColorArray
     **/
    public function clear()
    {
        unset($this->colors);

        return $this;
    }

    /**
     * @throws MissingElementException
     * @return Color
     **/
    public function getRandomTextColor()
    {
        if ($this->isEmpty())
            throw new MissingElementException('Not set colors in the object ');

        return $this->colors[array_rand($this->colors)];
    }

    public function isEmpty()
    {
        if (count($this->colors) == 0)
            return true;
        else
            return false;
    }

    public function getColors()
    {
        return $this->colors;
    }
}
