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
 * @see https://developers.facebook.com/docs/sharing/webmasters/images/
 * minimal image sizes - 200 x 200 px
 * for high resolution recommend use minimal 1 200 х 630 px
 * for publish with big image card minimal size 600 х 315 px
 * best FB ratio 1,91:1
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphImage extends OpenGraphVideo
{
    /**
     * @var string
     */
    protected string $name = 'image';
    /**
     * @var string
     */
    protected string $alt;

    /**
     * @param string $alt
     * @return static
     */
    public function setAlt(string $alt): static
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * @return array
     * @throws WrongArgumentException
     */
    public function getList(): array
    {
        $list = parent::getList();

        if (!empty($this->alt)) {
            $list[] = ['og:'.$this->name.':alt', $this->alt];
        }

        return $list;
    }
}