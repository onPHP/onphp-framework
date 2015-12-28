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
class Color implements Stringable
{
    private $red = 0;
    private $green = 0;
    private $blue = 0;

    public function __construct($hex)
    {
        $length = strlen($hex);

        Assert::isTrue($length <= 7, 'color must be #XXXXXX');

        if ($hex[0] == '#') {
            $hex = substr($hex, 1);
        }

        if ($length < 6) {
            $hex = str_pad($hex, 6, '0', STR_PAD_LEFT);
        }

        $this->red = hexdec($hex[0] . $hex[1]);
        $this->green = hexdec($hex[2] . $hex[3]);
        $this->blue = hexdec($hex[4] . $hex[5]);
    }

// valid values: #AABBCC, DDEEFF, A15B, etc.

    /**
     * @return Color
     **/
    public static function create($hex)
    {
        static $flyweightColors = [];

        if (isset($flyweightColors[$hex])) {
            return $flyweightColors[$hex];
        }

        $result = new self($hex);

        $flyweightColors[$hex] = $result;

        return $result;
    }

    /**
     * @return Color
     **/
    public function invertColor()
    {
        $this->setRed(255 - $this->getRed());
        $this->setBlue(255 - $this->getBlue());
        $this->setGreen(255 - $this->getGreen());

        return $this;
    }

    public function getRed()
    {
        return $this->red;
    }

    /**
     * @return Color
     **/
    public function setRed($red)
    {
        $this->red = $red;

        return $this;
    }

    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * @return Color
     **/
    public function setBlue($blue)
    {
        $this->blue = $blue;

        return $this;
    }

    public function getGreen()
    {
        return $this->green;
    }

    /**
     * @return Color
     **/
    public function setGreen($green)
    {
        $this->green = $green;

        return $this;
    }

    public function toString()
    {
        return sprintf('%02X%02X%02X', $this->red, $this->green, $this->blue);
    }
}

?>