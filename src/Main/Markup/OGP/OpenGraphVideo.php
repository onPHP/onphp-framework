<?php
/***************************************************************************
 *   Copyright (C) 2007 by Sergei V. Deriabin                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Markup\OGP;

use OnPHP\Core\Exception\WrongArgumentException;

/**
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphVideo extends OpenGraphStructure
{
    /**
     * @var string
     */
    protected string $video = 'video';
    /**
     * @var int
     */
    protected int $width;
    /**
     * @var int
     */
    protected int $height;

    /**
     * @param int $width
     * @return static
     */
    public function setWidth(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @param int $height
     * @return static
     */
    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return array
     * @throws WrongArgumentException
     */
    public function getList(): array
    {
        $list = parent::getList();

        if (!empty($this->width)) {
            $list[] = ['og:'.$this->name.':width', $this->width];
        }
        if (!empty($this->height)) {
            $list[] = ['og:'.$this->name.':height', $this->height];
        }

        return $list;
    }
}