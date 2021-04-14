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

/**
 * @see https://ogp.me/#type_music.radio_station
 *
 * creator - string - The creator of this station.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphRadio extends OpenGraphObject
{
    /**
     * @var string
     */
    protected string $namespace = 'music';
    /**
     * @var array
     */
    protected array $items = [
        'creator' => null,
    ];

    /**
     * OpenGraphRadio constructor.
     */
    public function __construct()
    {
        $this->type = new OpenGraphType(OpenGraphType::RADIO_ID);
    }
}