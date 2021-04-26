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
 * song - string[] - The song on this playlist
 * song:disc - integer >= 1 - Which disc of the album this song is on.
 * song:track - integer >= 1 - Which track this song is.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
abstract class OpenGraphSongObject extends OpenGraphObject
{
    /**
     * @var string
     */
    protected string $namespace = 'music';
    /**
     * @var array
     */
    protected array $items = [
        'song' => [],
        'song:disc' => [],
        'song:track' => []
    ];

    /**
     * @param string $name
     * @param mixed $value
     * @return static
     * @throws WrongArgumentException
     */
    public function set(string $name, $value): OpenGraphSongObject
    {
        if ($name == 'song') {
            $count = count($this->items['song']);
            for($i = $count - count($this->items['song:disc']); $i > 0; $i--) {
                $this->items['song:disc'][] = null;
            }
            for($i = $count - count($this->items['song:track']); $i > 0; $i--) {
                $this->items['song:track'][] = null;
            }
        }

	    if ($name == 'song:disc') {
		    Assert::isLesser(
			    count($this->items['song:disc']),
			    count($this->items['song']),
			    'add song before adding song:disc'
		    );
	    }

	    if ($name == 'song:track') {
		    Assert::isLesser(
			    count($this->items['song:track']),
			    count($this->items['song']),
			    'add song before adding song:track'
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
        foreach($this->items['song'] as $key => $value) {
            $list[] = [$this->namespace . ':song', $value];

            $disc = $this->items['song:disc'][$key] ?? null;
            $track = $this->items['song:track'][$key] ?? null;

            if (!empty($disc)) {
                $list[] = [$this->namespace . ':song:disc', $disc];
            }
            if (!empty($track)) {
                $list[] = [$this->namespace . ':song:track', $track];
            }
        }

        foreach($this->items as $key => $value) {
            if ($key == 'song' || $key == 'song:disc' || $key == 'song:track') {
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