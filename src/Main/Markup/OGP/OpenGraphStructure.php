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
use OnPHP\Main\Base\MimeType;

/**
 * Structured Properties
 * @see https://ogp.me/#structured
 *
 * @ingroup Markup
 * @ingroup OGP
 */
abstract class OpenGraphStructure
{
    /**
     * @var string
     */
    protected string $name;
    /**
     * @var string
     */
    protected string $url;
    /**
     * @var string
     */
    protected string $secureUrl;
    /**
     * @var MimeType
     */
    protected MimeType $type;

    /**
     * @return static
     */
    public static function create(): static
    {
        return new static;
    }

    /**
     * @param string $url
     * @param bool $secureSame
     * @return static
     */
    public function setUrl(string $url, bool $secureSame = true): static
    {
        $this->url = $url;
        if ($secureSame) {
            $this->secureUrl = $url;
        }

        return $this;
    }

    /**
     * @param string $url
     * @return static
     */
    public function setSecureUrl(string $url): static
    {
        $this->secureUrl = $url;

        return $this;
    }

    /**
     * @param MimeType $type
     * @return static
     */
    public function setType(MimeType $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     * @throws WrongArgumentException
     */
    public function getList(): array
    {
        Assert::isNotEmpty($this->url, 'url can not be blank');

        $list = [
            ['og:'.$this->name, $this->url]
        ];
        if (!empty($this->secureUrl)) {
            $list[] = ['og:'.$this->name.':secure_url', $this->secureUrl];
        }
        if (!empty($this->type)) {
            $list[] = ['og:'.$this->name.':type', $this->type->getMimeType()];
        }

        return $list;
    }
}