<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Vladimir A. Altuchov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Ip
 **/
class IpAddress implements Stringable, DialectString
{
    protected $longIp = null;

    /**
     * IpAddress constructor.
     * @param $ip
     */
    public function __construct($ip)
    {
        $this->setIp($ip);
    }

    /**
     * @param $ip
     * @return $this
     * @throws WrongArgumentException
     */
    public function setIp($ip)
    {
        $long = ip2long($ip);

        if ($long === false) {
            throw new WrongArgumentException('wrong ip given');
        }

        $this->longIp = $long;

        return $this;
    }

    /**
     * @param $ip
     * @return IpAddress
     */
    public static function createFromCutted($ip)
    {
        if (substr_count($ip, '.') < 3) {
            return self::createFromCutted($ip . '.0');
        }

        return new self($ip);
    }

    /**
     * @return null
     */
    public function getLongIp()
    {
        return $this->longIp;
    }

    /**
     * @param Dialect $dialect
     * @return mixed
     */
    public function toDialectString(Dialect $dialect)
    {
        return $dialect->quoteValue($this->toString());
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return long2ip($this->longIp);
    }

    /**
     * @return mixed
     */
    public function toSignedInt()
    {
        return TypesUtils::unsignedToSigned($this->longIp);
    }
}

