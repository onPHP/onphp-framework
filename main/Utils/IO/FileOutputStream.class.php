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
final class FileOutputStream extends OutputStream
{
    private $fd = null;

    public function __construct($nameOrFd, $append = false)
    {
        if (is_resource($nameOrFd)) {
            if (get_resource_type($nameOrFd) !== 'stream') {
                throw new IOException('not a file resource');
            }

            $this->fd = $nameOrFd;

        } else {
            try {
                $this->fd = fopen($nameOrFd, ($append ? 'a' : 'w') . 'b');

                Assert::isNotFalse(
                    $this->fd,
                    "File {$nameOrFd} must be exist"
                );
            } catch (BaseException $e) {
                throw new IOException($e->getMessage());
            }
        }
    }

    /**
     * @deprecated
     * @return FileOutputStream
     **/
    public static function create($nameOrFd, $append = false)
    {
        return new self($nameOrFd, $append);
    }

    public function __destruct()
    {
        try {
            $this->close();
        } catch (BaseException $e) {
            // boo.
        }
    }

    /**
     * @return FileOutputStream
     **/
    public function close()
    {
        fclose($this->fd);

        $this->fd = null;

        return $this;
    }

    /**
     * @return FileOutputStream
     **/
    public function write($buffer)
    {
        if (!$this->fd || $buffer === null) {
            return $this;
        }

        try {
            $written = fwrite($this->fd, $buffer);
        } catch (BaseException $e) {
            throw new IOException($e->getMessage());
        }

        if (!$written || $written < strlen($buffer)) {
            throw new IOException('disk full and/or buffer too large?');
        }

        return $this;
    }
}

?>