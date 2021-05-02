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
	 * @param string $name
	 * @param mixed $value
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function set(string $name, $value): OpenGraphMovie
	{
		if ($name == 'actor') {
			$count = count($this->items['actor']);
			for($i = $count - count($this->items['actor:role']); $i > 0; $i--) {
				$this->items['actor:role'][] = null;
			}
		}

		if ($name == 'actor:role') {
			Assert::isLesser(
				count($this->items['actor:role']),
				count($this->items['actor']),
				'add actor before adding actor:role'
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
        foreach($this->items['actor'] as $key => $actor) {
            $list[] = [$this->namespace . ':' . 'actor', $actor];
	        if (!empty($this->items['actor:role'][$key])) {
		        $list[] = [$this->namespace . ':' . 'actor:role', $this->items['actor:role'][$key]];
	        }
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