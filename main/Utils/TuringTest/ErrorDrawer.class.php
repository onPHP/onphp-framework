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
class ErrorDrawer
{
    const FONT_SIZE = 4;

    private static $drawError = true;
    private $turingImage;

    public function __construct(TuringImage $turingImage)
    {
        $this->turingImage = $turingImage;
    }

    /**
     * @return ErrorDrawer
     **/
    public function draw($string = 'ERROR!')
    {
        if (!ErrorDrawer::isDrawError())
            return $this;

        $y = round(
            $this->turingImage->getHeight() / 2
            - imagefontheight(ErrorDrawer::FONT_SIZE) / 2
        );

        $textWidth = imagefontwidth(ErrorDrawer::FONT_SIZE) * strlen($string);

        if ($this->turingImage->getWidth() > $textWidth)
            $x = round(($this->turingImage->getWidth() - $textWidth) / 2);
        else
            $x = 0;

        $color = $this->turingImage->getOneCharacterColor();

        imagestring(
            $this->turingImage->getImageId(),
            ErrorDrawer::FONT_SIZE,
            $x,
            $y,
            $string,
            $color
        );

        return $this;
    }

    public static function isDrawError()
    {
        return self::$drawError;
    }

    public static function setDrawError($drawError = false)
    {
        self::$drawError = $drawError;
    }
}