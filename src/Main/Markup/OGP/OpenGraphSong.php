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

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;

/**
 * @see https://ogp.me/#type_music.song
 *
 * duration - integer >=1 - The song's length in seconds.
 * album - string[] - The album this song is from.
 * album:disc - integer[] >= 1 - Which disc of the album this song is on.
 * album:track - integer[] >= 1 - Which track this song is.
 * musician - string[] - The musician that made this song.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphSong extends OpenGraphObject
{
    /**
     * @var string
     */
    protected string $namespace = 'music';
    /**
     * @var array
     */
    protected array $items = [
        'duration' => null,
        'album' => [],
        'album:disc' => [],
        'album:track' => [],
        'musician' => []
    ];

    /**
     * OpenGraphSong constructor.
     */
    public function __construct()
    {
        $this->type = new OpenGraphType(OpenGraphType::SONG_ID);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return static
     * @throws WrongArgumentException
     */
    public function set(string $name, mixed $value): static
    {
        if ($name == 'album') {
            $count = count($this->items['album']);
            for($i = $count - count($this->items['album:disc']); $i > 0; $i--) {
                $this->items['album:disc'][] = null;
            }
            for($i = $count - count($this->items['album:track']); $i > 0; $i--) {
                $this->items['album:track'][] = null;
            }
        }

	    if ($name == 'album:disc') {
		    Assert::isLesser(
			    count($this->items['album:disc']),
			    count($this->items['album']),
			    'add album before adding album:disc'
		    );
	    }

	    if ($name == 'album:track') {
		    Assert::isLesser(
			    count($this->items['album:track']),
			    count($this->items['album']),
			    'add album before adding album:track'
		    );
	    }

        return parent::set($name, $value);
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        $list = [];
        foreach($this->items['album'] as $key => $value) {
            $list[] = [$this->namespace . ':album', $value];

            $disc = $this->items['album:disc'][$key] ?? null;
            $track = $this->items['album:track'][$key] ?? null;

            if (!empty($disc)) {
                $list[] = [$this->namespace . ':album:disc', $disc];
            }
            if (!empty($track)) {
                $list[] = [$this->namespace . ':album:track', $track];
            }
        }

        foreach($this->items as $key => $value) {
            if ($key == 'album' || $key == 'album:disc' || $key == 'album:track') {
                continue;
            }

            if (is_array($value)) {
                foreach (array_filter($value) as $item) {
	                $list[] = [$this->namespace . ':' . $key, $item];
                }
            } elseif(!empty($value)) {
                $list[] = [$this->namespace . ':' . $key, $value];
            }
        }

        return $list;
    }
}