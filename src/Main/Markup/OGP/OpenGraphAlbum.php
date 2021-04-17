<?php
/***************************************************************************
 *   Copyright (C) 2021 by Sergei V. Deriabin                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Markup\OGP;

/**
 * @see https://ogp.me/#type_music.album
 *
 * song - string[] - The song on this album.
 * song:disc - integer[] >= 1 - Which disc of the album this song is on.
 * song:track - integer[] >= 1 - Which track this song is.
 * musician - string[] - The musician that made this song.
 * release_date - string datetime ISO 8601 - The date the album was released.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphAlbum extends OpenGraphSongObject
{
	/**
	 * @var string
	 */
    protected string $namespace = 'music';

    /**
     * OpenGraphAlbum constructor.
     */
    public function __construct()
    {
        $this->type = new OpenGraphType(OpenGraphType::ALBUM_ID);
        $this->items += [
            'musician' => [],
            'release_date' => null
        ];
    }
}