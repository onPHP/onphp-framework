<?php
/***************************************************************************
 *   Copyright (C) 2007 by Vladimir A. Altuchov                            *
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
class FileReader extends Reader
{
    private $fd = null;

    public function __construct($fileName)
    {
        if (!is_readable($fileName)) {
            throw new WrongStateException("Can not read {$fileName}");
        }

        try {
            $this->fd = fopen($fileName, 'rt');
        } catch (BaseException $e) {
            throw new IOException($e->getMessage());
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        try {
            $this->close();
        } catch (BaseException $e) {
            // boo.
        }
    }

    /**
     * @return $this
     * @throws IOException
     */
    public function close()
    {
        if (!fclose($this->fd)) {
            throw new IOException('failed to close the file');
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEof()
    {
        return feof($this->fd);
    }

    /**
     * @return bool
     */
    public function markSupported() : bool
    {
        return true;
    }

    /**
     * @return FileReader
     **/
    public function mark()
    {
        $this->mark = ftell($this->fd);

        return $this;
    }

    /**
     * @return $this
     * @throws IOException
     */
    public function reset()
    {
        if (fseek($this->fd, $this->mark) < 0) {
            throw new IOException(
                'mark has been invalidated'
            );
        }

        return $this;
    }

    /**
     * @param $length
     * @return null|string
     */
    public function read($length)
    {
        $result = null;

        for ($i = 0; $i < $length; $i++) {
            if (
                ($char = fgetc($this->fd)) === false
            ) {
                break;
            }

            $result .= $char;
        }

        return $result;
    }
}

