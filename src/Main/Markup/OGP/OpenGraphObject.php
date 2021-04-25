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
 * Object Types
 * @see https://ogp.me/#types
 *
 * @ingroup Markup
 * @ingroup OGP
 */
abstract class OpenGraphObject
{
    /**
     * @var string
     */
    protected string $namespace;
    /**
     * @var OpenGraphType
     */
    protected OpenGraphType $type;
    /**
     * @var array
     */
    protected array $items = [];

	/**
	 * @return static
	 */
	public static function create(): OpenGraphObject
	{
		return new static;
	}

    /**
     * @return array
     */
    public function getList(): array
    {
        $list = [];
        foreach($this->items as $key => $value) {
            if (is_array($value)) {
                foreach (array_filter($value) as $item) {
                    $list[] = [(empty($this->namespace) ? '' : $this->namespace . ':') . $key, $item];
                }
            } elseif(!empty($value)) {
                $list[] = [(empty($this->namespace) ? '' : $this->namespace . ':') . $key, $value];
            }
        }

        return $list;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return static
     * @throws WrongArgumentException
     */
    public function set(string $name, $value): OpenGraphObject
    {
        Assert::isIndexExists($this->items, $name, "there is no property {$name} in " . get_class($this));

        if (is_array($this->items[$name])) {
            $this->items[$name][] = $value;
        } else {
            $this->items[$name] = $value;
        }


        return $this;
    }

    /**
     * @return OpenGraphType
     */
    final public function getType(): OpenGraphType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    final public function getNamespace(): string
    {
        return $this->namespace;
    }
}