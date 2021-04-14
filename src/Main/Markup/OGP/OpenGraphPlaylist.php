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
 * @see https://ogp.me/#type_music.playlist
 *
 * song - string[] - The song on this playlist
 * song:disc - integer >= 1 - Which disc of the album this song is on.
 * song:track - integer >= 1 - Which track this song is.
 * creator - string - The creator of this playlist.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphPlaylist extends OpenGraphSongObject
{
    protected string $namespace = 'music';

    /**
     * OpenGraphPlaylist constructor.
     */
    public function __construct()
    {
        $this->type = new OpenGraphType(OpenGraphType::PLAYLIST_ID);
        $this->items['creator'] = null;
    }
}