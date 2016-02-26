<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Utils
 **/
class StringReader extends Reader
{
    private $string = null;
    private $length = null;

    private $next = 0;
    private $mark = 0;

    public function __construct($string)
    {
        $this->string = $string;
        $this->length = mb_strlen($this->string);
    }

    /**
     * @return StringReader
     **/
    public function close()
    {
        $this->string = null;

        return $this;
    }

    /**
     * @return StringReader
     **/
    public function mark()
    {
        $this->ensureOpen();

        $this->mark = $this->next;

        return $this;
    }

    /**
     * @throws IOException
     */
    private function ensureOpen()
    {
        if ($this->string === null) {
            throw new IOException('Stream closed');
        }
    }

    /**
     * @return bool
     */
    public function markSupported()
    {
        return true;
    }

    /**
     * @return StringReader
     **/
    public function reset()
    {
        $this->ensureOpen();

        $this->next = $this->mark;

        return $this;
    }

    /**
     * @param $count
     * @return int|mixed
     * @throws IOException
     */
    public function skip($count)
    {
        $this->ensureOpen();

        if ($this->isEof()) {
            return 0;
        }

        $actualSkip =
            max(
                -$this->next,
                min($this->length - $this->next, $count)
            );

        $this->next += $actualSkip;

        return $actualSkip;
    }

    /**
     * @return bool
     */
    public function isEof()
    {
        return ($this->next >= $this->length);
    }

    /**
     * @return null|string
     */
    public function getWhole()
    {
        return $this->read($this->length - $this->next);
    }

    /* void */

    /**
     * @param $count
     * @return null|string
     * @throws IOException
     */
    public function read($count)
    {
        $this->ensureOpen();

        if ($this->next >= $this->length) {
            return null;
        }

        $result = mb_substr($this->string, $this->next, $count);

        $this->next += $count;

        return $result;
    }
}

