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
class TextMessage implements Message
{
    private $timestamp = null;
    private $text = null;

    /**
     * TextMessage constructor.
     * @param Timestamp|null $timestamp
     */
    public function __construct(Timestamp $timestamp = null)
    {
        $this->timestamp = $timestamp ?: Timestamp::makeNow();
    }

    /**
     * @return null|Timestamp
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param Timestamp $timestamp
     * @return $this
     */
    public function setTimestamp(Timestamp $timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return null
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}


