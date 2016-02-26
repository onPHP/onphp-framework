<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Ivan Y. Khvostishkov                       *
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
class FileInputStream extends InputStream
{
    private $fd = null;

    private $mark = null;

    public function __construct($nameOrFd)
    {
        if (is_resource($nameOrFd)) {
            if (get_resource_type($nameOrFd) !== 'stream') {
                throw new IOException('not a file resource');
            }

            $this->fd = $nameOrFd;

        } else {
            try {
                $this->fd = fopen($nameOrFd, 'rb');
            } catch (BaseException $e) {
                throw new IOException($e->getMessage());
            }
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
     * @return FileInputStream
     **/
    public function mark()
    {
        $this->mark = $this->getOffset();

        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return ftell($this->fd);
    }

    /**
     * @return bool
     */
    public function markSupported()
    {
        return true;
    }

    /**
     * @return FileInputStream
     **/
    public function reset()
    {
        return $this->seek($this->mark);
    }

    /**
     * @param $offset
     * @return $this
     * @throws IOException
     */
    public function seek($offset)
    {
        if (fseek($this->fd, $offset) < 0) {
            throw new IOException(
                'mark has been invalidated'
            );
        }

        return $this;
    }

    /**
     * @param $length
     * @return null|string
     * @throws IOException
     */
    public function read($length)
    {
        return $this->realRead($length);
    }

    /**
     * @param $length
     * @param bool|false $string
     * @return null|string
     * @throws IOException
     */
    public function realRead($length, $string = false)
    {
        $result = $string
            ? (
            $length === null
                ? fgets($this->fd)
                : fgets($this->fd, $length)
            )
            : fread($this->fd, $length);

        if ($string && $result === false && feof($this->fd)) {
            $result = null;
        } // fgets returns false on eof

        if ($result === false) {
            throw new IOException('failed to read from file');
        }

        if ($result === '') {
            $result = null;
        } // eof

        return $result;
    }

    /**
     * @param null $length
     * @return null|string
     * @throws IOException
     */
    public function readString($length = null)
    {
        return $this->realRead($length, true);
    }
}