<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
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
class BufferedInputStream extends InputStream
{
    private $runAheadBytes = 0;

    private $in = null;
    private $closed = false;

    private $buffer = null;
    private $bufferLength = 0;

    private $position = 0;
    private $markPosition = null;

    public function __construct(InputStream $in)
    {
        $this->in = $in;
    }

    /**
     * @return BufferedInputStream
     **/
    public static function create(InputStream $in)
    {
        return new self($in);
    }

    /**
     * @return BufferedInputStream
     **/
    public function close()
    {
        $this->closed = true;

        return $this;
    }

    public function isEof()
    {
        return $this->in->isEof();
    }

    public function markSupported()
    {
        return true;
    }

    /**
     * @return BufferedInputStream
     **/
    public function mark()
    {
        $this->markPosition = $this->position;

        return $this;
    }

    /**
     * @return BufferedInputStream
     **/
    public function reset()
    {
        $this->position = $this->markPosition;

        return $this;
    }

    /**
     * @return BufferedInputStream
     **/
    public function setRunAheadBytes($runAheadBytes)
    {
        $this->runAheadBytes = $runAheadBytes;

        return $this;
    }

    public function read($count)
    {
        if ($this->closed) {
            return null;
        }

        $remainingCount = $count;
        $availableCount = $this->available();

        if ($remainingCount <= $availableCount) {
            $readFromBuffer = $count;
        } else {
            $readFromBuffer = $availableCount;
        }

        $result = null;

        if ($readFromBuffer > 0) {
            $result = substr(
                $this->buffer, $this->position, $readFromBuffer
            );

            $this->position += $readFromBuffer;
            $remainingCount -= $readFromBuffer;
        }

        if ($remainingCount > 0) {

            $readAtOnce = ($remainingCount < $this->runAheadBytes)
                ? $this->runAheadBytes
                : $remainingCount;

            $readBytes = $this->in->read($readAtOnce);
            $readBytesLength = strlen($readBytes);

            if ($readBytesLength > 0) {
                $this->buffer .= $readBytes;
                $this->bufferLength += $readBytesLength;

                if ($readBytesLength <= $remainingCount) {
                    $this->position += $readBytesLength;
                    $result .= $readBytes;
                } else {
                    $this->position += $remainingCount;
                    $result .= substr($readBytes, 0, $remainingCount);
                }
            }
        }

        return $result;
    }

    public function available()
    {
        if ($this->closed) {
            return 0;
        }

        return ($this->bufferLength - $this->position);
    }
}

