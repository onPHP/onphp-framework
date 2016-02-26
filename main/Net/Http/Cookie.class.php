<?php
/***************************************************************************
 *   Copyright (C) 2008 by Evgeny V. Kokovikhin                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/*$id$*/

/**
 * @ingroup Http
 **/
class Cookie extends CollectionItem
{
    private $name = null;
    private $value = null;
    private $expire = 0;
    private $path = null;
    private $domain = null;
    private $secure = false;
    private $httpOnly = false;

    /**
     * Cookie constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->id = $this->name = $name;
    }

    /**
     * @param $expire
     * @return $this
     * @throws WrongArgumentException
     */
    public function setMaxAge($expire)
    {
        Assert::isInteger($expire);

        $this->expire = $expire;

        return $this;
    }

    /**
     * @return bool
     * @throws WrongStateException
     */
    public function httpSet()
    {
        if (headers_sent()) {
            throw new WrongStateException('headers already send');
        }

        return
            setcookie(
                $this->getName(),
                $this->getValue(),
                ($this->getMaxAge() === 0)
                    ? 0
                    : $this->getMaxAge() + time(),
                $this->getPath(),
                $this->getDomain(),
                $this->getSecure(),
                $this->getHttpOnly()
            );
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxAge() : integer
    {
        return $this->expire;
    }

    /**
     * @return null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $path
     * @return $this
     * @throws WrongArgumentException
     */
    public function setPath($path)
    {
        Assert::isString($path);

        $this->path = $path;

        return $this;
    }

    /**
     * @return null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain($domain)
    {
        Assert::isString($domain);

        $this->domain = $domain;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @param bool|true $secure
     * @return $this
     */
    public function setSecure($secure = true)
    {
        $this->secure = (boolean) $secure;

        return $this;
    }

    /**
     * @return bool
     */
    public function getHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * @param bool|true $httpOnly
     * @return $this
     */
    public function setHttpOnly($httpOnly = true)
    {
        $this->httpOnly = (boolean) $httpOnly;

        return $this;
    }
}

