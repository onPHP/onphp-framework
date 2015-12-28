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
class SocketInputStream extends InputStream
{
    /**
     * NOTE: if socket timeout is 1 second, we can block here
     * over abt 15 seconds. See conventions of InputStream.
     *
     * You must set reliable timeout for socket operations if you want to
     * avoid fatal error on max_execution_time and you must make sure the
     * length is not too large to read it at once from your physical
     * channel.
     **/
    const READ_ATTEMPTS = 15; // should be enough for everyone (C)

    private $socket = null;
    private $eof = false;

    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    public function isEof()
    {
        return $this->eof;
    }

    public function read($length)
    {
        if ($length == 0 || $this->eof) {
            return null;
        }

        try {
            $result = $this->socket->read($length);

            if ($result === null) {
                $this->eof = true;
            }

            $i = 0;

            while (
                !$this->eof
                && strlen($result) < $length
                && ($i < self::READ_ATTEMPTS)
            ) {
                // 0.1s sleep insurance if something wrong with socket
                usleep(100000);

                $remainingLength = $length - strlen($result);

                // NOTE: ignoring timeouts here
                $nextPart = $this->socket->read($remainingLength);

                if ($nextPart !== null) {
                    $result .= $nextPart;
                } else {
                    $this->eof = true;
                }

                ++$i;
            }
        } catch (NetworkException $e) {
            throw new IOException($e->getMessage());
        }

        if (!$this->eof && strlen($result) < $length) {
            throw new IOException(
                'connection is too slow or length is too large?'
            );
        }

        return $result;
    }
}

