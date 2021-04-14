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

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;

/**
 * Class OpenGraphMovie
 * @see https://ogp.me/#type_video.movie
 *
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
class OpenGraphMovie extends OpenGraphObject
{
    /**
     * @var string
     */
    protected string $namespace = 'video';
    /**
     * @var array
     */
    protected array $items = [
        'actor' => [],
        'actor:role' => [],
        'director' => [],
        'writer' => [],
        'duration' => null,
        'release_date' => null,
        'tag' => [],
    ];

    /**
     * OpenGraphMovie constructor.
     */
    public function __construct()
    {
        $this->type = new OpenGraphType(OpenGraphType::MOVIE_ID);
    }

    /**
     * @return array
     * @throws WrongArgumentException
     */
    public function getList(): array
    {
        Assert::isTrue(
            count($this->items['actor']) == count($this->items['actor:role']),
            'actor items not equal actor:role items'
        );

        $list = [];
        foreach($this->items['actor'] as $key => $actor) {
            $list[] = [$this->namespace . ':' . 'actor', $actor];
            $list[] = [$this->namespace . ':' . 'actor:role', $this->items['actor:role'][$key]];
        }

        foreach($this->items as $key => $value) {
            if ($key == 'actor' || $key == 'actor:role') {
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