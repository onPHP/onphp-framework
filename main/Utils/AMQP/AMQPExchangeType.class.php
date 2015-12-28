<?php

/***************************************************************************
 *   Copyright (C) 2011 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
final class AMQPExchangeType extends Enumeration
{
    const DIRECT = 1;
    const FANOUT = 2;
    const TOPIC = 3;
    const HEADER = 4;

    protected $names = [
        self::DIRECT => "direct",
        self::FANOUT => "fanout",
        self::TOPIC => "topic",
        self::HEADER => "header"
    ];

    public function getDefault()
    {
        return self::DIRECT;
    }

    public function isDirect()
    {
        return $this->id == self::DIRECT;
    }

    public function isFanout()
    {
        return $this->id == self::FANOUT;
    }

    public function isTopic()
    {
        return $this->id == self::TOPIC;
    }

    public function isHeader()
    {
        return $this->id == self::HEADER;
    }
}

