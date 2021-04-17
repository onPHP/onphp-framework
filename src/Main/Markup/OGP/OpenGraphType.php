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

use OnPHP\Core\Base\Enum;

/**
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphType extends Enum
{
    const WEBSITE_ID = 1;
    const SONG_ID = 2;
    const ALBUM_ID = 3;
    const PLAYLIST_ID = 4;
    const RADIO_ID = 5;
    const MOVIE_ID = 6;
    const EPISODE_ID = 7;
    const TV_SHOW_ID = 8;
    const VIDEO_OTHER_ID = 9;
    const ARTICLE_ID = 10;
    const BOOK_ID = 11;
    const PROFILE_ID = 12;

	/**
	 * @var string[]
	 */
    protected static $names = [
        self::WEBSITE_ID => 'website',
        self::SONG_ID => 'music.song',
        self::ALBUM_ID => 'music.album',
        self::PLAYLIST_ID => 'music.playlist',
        self::RADIO_ID => 'music.radio_station',
        self::MOVIE_ID => 'video.movie',
        self::EPISODE_ID => 'video.episode',
        self::TV_SHOW_ID => 'video.tv_show',
        self::VIDEO_OTHER_ID => 'video.other',
        self::ARTICLE_ID => 'article',
        self::BOOK_ID => 'book',
        self::PROFILE_ID => 'profile'
    ];

	/**
	 * @var string[]
	 */
    protected array $namespaces = [
        self::WEBSITE_ID => 'https://ogp.me/ns/website#',
        self::SONG_ID => 'https://ogp.me/ns/music#',
        self::ALBUM_ID => 'https://ogp.me/ns/music#',
        self::PLAYLIST_ID => 'https://ogp.me/ns/music#',
        self::RADIO_ID => 'https://ogp.me/ns/music#',
        self::MOVIE_ID => 'https://ogp.me/ns/video#',
        self::EPISODE_ID => 'https://ogp.me/ns/video#',
        self::TV_SHOW_ID => 'https://ogp.me/ns/video#',
        self::VIDEO_OTHER_ID => 'https://ogp.me/ns/video#',
        self::ARTICLE_ID => 'https://ogp.me/ns/article#',
        self::BOOK_ID => 'https://ogp.me/ns/book#',
        self::PROFILE_ID => 'https://ogp.me/ns/profile#'
    ];

	/**
	 * @return string
	 */
    public function getNamespace(): string
    {
        return $this->namespaces[$this->getId()];
    }
}