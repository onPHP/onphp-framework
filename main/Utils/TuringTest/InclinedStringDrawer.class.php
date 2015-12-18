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
final class InclinedStringDrawer extends TextDrawer
{
    const MAX_ANGLE = 70;

    /**
     * @param $string
     * @return InclinedStringDrawer
     */
    public function draw($string)
    {
        $textWidth = $this->getTextWidth($string);
        $textHeight = $this->getMaxCharacterHeight();

        if ($textWidth < $this->getTuringImage()->getHeight()) {
            $maxAngle = 45;
        } else {
            $maxAngle =
                rad2deg(
                    asin(
                        ($this->getTuringImage()->getHeight() - $textHeight)
                        / $textWidth
                    )
                );
        }

        $angle = mt_rand(-$maxAngle / 2, $maxAngle / 2);

        if ($angle > self::MAX_ANGLE)
            $angle = self::MAX_ANGLE;

        if ($angle < -self::MAX_ANGLE)
            $angle = -self::MAX_ANGLE;

        if ($this->getTuringImage()->getWidth() > $textWidth) {
            $x = round(
                (
                    ($this->getTuringImage()->getWidth() - $textWidth)
                    * cos(deg2rad($angle))
                )
                / 2
            );

            $y = round(
                (
                    ($this->getTuringImage()->getHeight() + $textWidth)
                    * sin(deg2rad($angle))
                )
                / 2
                + ($textHeight / 2)
            );

            for ($i = 0, $length = strlen($string); $i < $length; ++$i) {
                $character = $string[$i];

                $this->drawCraracter($angle, $x, $y, $character);

                $charWidth =
                    $this->getStringWidth($character)
                    + $this->getSpace();

                $y -= $charWidth * sin(deg2rad($angle));
                $x += $charWidth * cos(deg2rad($angle));
            }
        } else
            return $this->showError();

        return $this;
    }
}