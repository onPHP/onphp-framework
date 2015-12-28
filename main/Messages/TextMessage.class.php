<?php

/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
final class TextMessage implements Message
{
    private $timestamp = null;
    private $text = null;

    public function __construct(Timestamp $timestamp = null)
    {
        $this->timestamp = $timestamp ?: Timestamp::makeNow();
    }

    /**
     * @deprecated
     * @param Timestamp|null $timestamp
     * @return TextMessage
     */
    public static function create(Timestamp $timestamp = null)
    {
        return new self($timestamp);
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function setTimestamp(Timestamp $timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}


