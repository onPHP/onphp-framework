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
 * Class OpenGraphTvShow
 * A multi-episode TV show.
 * @see https://ogp.me/#type_video.tv_show
 *
 * A multi-episode TV show.
 * actor - string[] - Actors in the movie.
 * actor:role - string[] - The role they played.
 * director - string[] - Directors of the movie.
 * writer - string[] - Writers of the movie.
 * duration - integer >=1 - The movie's length in seconds.
 * release_date - string datetime ISO 8601 - The date the movie was released.
 * tag - string[] - Tag words associated with this book.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphTvShow extends OpenGraphMovie
{
    /**
     * OpenGraphTvShow constructor.
     */
    public function __construct()
    {
        $this->type = new OpenGraphType(OpenGraphType::TV_SHOW_ID);
    }
}