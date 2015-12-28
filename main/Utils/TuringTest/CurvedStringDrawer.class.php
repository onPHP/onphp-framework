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
class CurvedStringDrawer extends TextDrawer
{
    const MAX_ANGLE_CHANGE = 40;
    const MAX_ANGLE = 45;
    const MAX_VERTIVAL_POSITION_CHANGE = 1.5;

    /**
     * @return CurvedStringDrawer
     **/
    public function draw($string)
    {
        $turingImage = $this->getTuringImage();

        $textWidth =
            $this->getTextWidth($string)
            + (strlen($string) - 1)
            * $this->getSize() / 2;

        if ($turingImage->getWidth() <= $textWidth) {
            return $this->showError();
        }

        $angle =
            mt_rand(
                -CurvedStringDrawer::MAX_ANGLE_CHANGE / 2,
                CurvedStringDrawer::MAX_ANGLE_CHANGE / 2
            );

        $maxHeight = $this->getMaxCharacterHeight();

        $y = round(($turingImage->getHeight() + $maxHeight) / 2);
        $x = round(($turingImage->getWidth() - $textWidth) / 2);

        for ($size = strlen($string), $i = 0; $i < $size; ++$i) {
            $angle +=
                mt_rand(
                    -CurvedStringDrawer::MAX_ANGLE_CHANGE / 2,
                    CurvedStringDrawer::MAX_ANGLE_CHANGE / 2
                );

            if ($angle > CurvedStringDrawer::MAX_ANGLE) {
                $angle = CurvedStringDrawer::MAX_ANGLE;
            } elseif ($angle < -CurvedStringDrawer::MAX_ANGLE) {
                $angle = -CurvedStringDrawer::MAX_ANGLE;
            }

            $y +=
                mt_rand(
                    -$turingImage->getHeight() / 2,
                    $turingImage->getHeight() / 2
                );

            if ($y < ($maxHeight * CurvedStringDrawer::MAX_VERTIVAL_POSITION_CHANGE)) {
                $y = $maxHeight * CurvedStringDrawer::MAX_VERTIVAL_POSITION_CHANGE;
            }

            if ($y > ($turingImage->getHeight() - $maxHeight)) {
                $y = $turingImage->getHeight() - $maxHeight;
            }

            $character = $string[$i];
            $this->drawCraracter($angle, $x, $y, $character);

            $x += $this->getStringWidth($character) + $this->getSize() / 2;
        }

        return $this;
    }
}
