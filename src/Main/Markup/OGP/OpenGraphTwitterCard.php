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
 * Twitter Cards
 * @see https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/markup
 * @see https://cards-dev.twitter.com/validator
 *
 * @ingroup Markup
 * @ingroup OGP
 */
abstract class OpenGraphTwitterCard
{
    const NAMESPACE = 'twitter';
    /**
     * @var string
     */
    protected string $type;

    /**
     * @var array
     */
    protected array $items = [
        'site' => null,
        'description' => null
    ];

	/**
	 * @return static
	 */
    public static function create(): static
    {
    	return new static;
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        $item = $this->items + ['card' => $this->type];
        $list = [];

        foreach(array_filter($item) as $key => $value) {
            $list[] = [self::NAMESPACE . ':' . $key, $value];
        }

        return $list;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return static
     * @throws WrongArgumentException
     */
    final public function set(string $name, mixed $value): static
    {
        Assert::isIndexExists($this->items, $name, "there is no property {$name} in " . get_class($this));
        $this->items[$name] = $value;

        return $this;
    }
}