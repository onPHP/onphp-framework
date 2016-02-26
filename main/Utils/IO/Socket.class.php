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
class Socket
{
    const DEFAULT_TIMEOUT = 1000; // milliseconds

    const EAGAIN = 11;    // timeout, try again

    private $socket = null;
    private $connected = false;

    private $host = null;
    private $port = null;

    private $inputStream = null;
    private $outputStream = null;

    private $closed = false;
    private $inputShutdown = false;
    private $outputShutdown = false;

    // milliseconds
    private $readTimeout = null;
    private $writeTimeout = null;

    /**
     * Socket constructor.
     */
    public function __construct()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($this->socket === false) {
            throw new NetworkException(
                'socket creating failed: '
                . socket_strerror(socket_last_error())
            );
        }

        $this->inputStream = new SocketInputStream($this);

        $this->outputStream = new SocketOutputStream($this);
    }

    /**
     *
     */
    public function __destruct()
    {
        if (!$this->closed) {
            try {
                $this->close();
            } catch (BaseException $e) {
                /* boo! */
            }
        }
    }

    /**
     * @return Socket
     **/
    public function close()
    {
        socket_set_option(
            $this->socket,
            SOL_SOCKET,
            SO_LINGER,
            ['l_onoff' => 1, 'l_linger' => 1]
        );

        if (!$this->inputShutdown) {
            $this->shutdownInput();
        }

        if (!$this->outputShutdown) {
            $this->shutdownOutput();
        }

        socket_close($this->socket);

        $this->closed = true;

        return $this;
    }

    /**
     * @return Socket
     **/
    public function shutdownInput()
    {
        try {
            socket_shutdown($this->socket, 0);
        } catch (BaseException $e) {/*socket was closed*/
        }

        $this->inputShutdown = true;

        return $this;
    }

    /**
     * @return Socket
     **/
    public function shutdownOutput()
    {
        try {
            socket_shutdown($this->socket, 1);
        } catch (BaseException $e) {/*socket was closed*/
        }

        $this->outputShutdown = true;

        return $this;
    }

    /**
     * @return null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return Socket
     **/
    public function setHost($host)
    {
        Assert::isNull($this->host);

        $this->host = $host;

        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return Socket
     **/
    public function setPort($port)
    {
        Assert::isNull($this->port);

        $this->port = $port;

        return $this;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * @return SocketInputStream
     **/
    public function getInputStream()
    {
        $this->checkRead();

        return $this->inputStream;
    }

    private function checkRead()
    {
        if ($this->closed || !$this->connected || $this->inputShutdown) {
            throw new NetworkException(
                'cannod read from socket: '
                . 'it is closed, not connected, or has been shutdown'
            );
        }
    }

    /**
     * @return SocketOutputStream
     **/
    public function getOutputStream()
    {
        $this->checkWrite();

        return $this->outputStream;
    }

    // NOTE: return value may slightly differ from $this->readTimeout

    /**
     * @throws NetworkException
     */
    private function checkWrite()
    {
        if ($this->closed || !$this->connected || $this->inputShutdown) {
            throw new NetworkException(
                'cannod write to socket: '
                . 'it is closed, not connected, or has been shutdown'
            );
        }
    }

    //  return value may slightly differ from $this->writeTimeout

    /**
     * @param int $connectTimeout
     * @return $this
     * @throws NetworkException
     * @throws WrongArgumentException
     */
    public function connect($connectTimeout = self::DEFAULT_TIMEOUT)
    {
        Assert::isTrue(
            isset($this->host) && isset($this->port),
            'set host and port first'
        );

        // TODO: assuming we are in blocking mode
        // for non-blocking mode this method must throw an exception,
        // use non-blocking socket channels instead

        socket_set_nonblock($this->socket);

        try {
            socket_connect($this->socket, $this->host, $this->port);
        } catch (BaseException $e) {
            /* yum-yum */
        }

        socket_set_block($this->socket);

        $r = [$this->socket];
        $w = [$this->socket];
        $e = [$this->socket];

        switch (
        socket_select(
            $r, $w, $e,
            self::getSeconds($connectTimeout),
            self::getMicroseconds($connectTimeout)
        )
        ) {
            case 0:
                throw new NetworkException(
                    "unable to connect to '{$this->host}:{$this->port}': "
                    . "connection timed out"
                );

            case 1:
                $this->connected = true;
                break;

            case 2:
                // yanetut
                throw new NetworkException(
                    "unable to connect to '{$this->host}:{$this->port}': "
                    . 'connection refused'
                );
        }

        if (!$this->readTimeout) {
            $this->setReadTimeout(self::DEFAULT_TIMEOUT);
        }

        if (!$this->writeTimeout) {
            $this->setWriteTimeout(self::DEFAULT_TIMEOUT);
        }

        return $this;
    }

    /**
     * @param $timeout
     * @return int
     */
    private static function getSeconds($timeout) : int
    {
        return (int) ($timeout / 1000);
    }

    /**
     * @param $timeout
     * @return int
     */
    private static function getMicroseconds($timeout) : int
    {
        return (int) ($timeout % 1000 * 1000);
    }

    /**
     * @return Socket
     **/
    public function setTimeout($timeout)
    {
        $this->setReadTimeout($timeout);
        $this->setWriteTimeout($timeout);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReadTimeout()
    {
        $timeVal = socket_get_option($this->socket, SOL_SOCKET, SO_RCVTIMEO);

        return $timeVal['sec'] * 1000 + (int) ($timeVal['usec'] / 1000);
    }

    /**
     * @return Socket
     **/
    public function setReadTimeout($timeout)
    {
        $timeVal = [
            'sec' => self::getSeconds($timeout),
            'usec' => self::getMicroseconds($timeout)
        ];

        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, $timeVal);

        $this->readTimeout = $timeout;

        return $this;
    }

    public function getWriteTimeout()
    {
        $timeVal = socket_get_option($this->socket, SOL_SOCKET, SO_RCVTIMEO);

        return $timeVal['sec'] * 1000 + (int) ($timeVal['usec'] / 1000);
    }

    /**
     * @return Socket
     **/
    public function setWriteTimeout($timeout)
    {
        $timeVal = [
            'sec' => self::getSeconds($timeout),
            'usec' => self::getMicroseconds($timeout)
        ];

        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, $timeVal);

        $this->readTimeout = $timeout;

        return $this;
    }

    /**
     * returns 8-bit string or false on timeout or null on eof
     **/
    public function read($length)
    {
        $this->checkRead();

        socket_clear_error($this->socket);

        try {
            $result = socket_read($this->socket, $length);
        } catch (BaseException $e) {
            // probably connection reset by peer
            $result = false;
        }

        if ($result === false && !$this->isTimedOut()) {
            throw new NetworkException(
                'socket reading failed: '
                . socket_strerror(socket_last_error())
            );
        } elseif ($result === '') {
            return null;
        } // eof

        return $result;
    }

    /* void */

    /**
     * @return bool
     */
    public function isTimedOut() : bool
    {
        return (socket_last_error($this->socket) === self::EAGAIN);
    }

    /* void */

    /**
     * returns number of written bytes or false on timeout
     **/
    public function write($buffer, $length = null)
    {
        $this->checkWrite();

        socket_clear_error($this->socket);

        try {
            if ($length === null) {
                $result = socket_write($this->socket, $buffer);
            } else {
                $result = socket_write($this->socket, $buffer, $length);
            }

        } catch (BaseException $e) {
            // probably connection reset by peer
            $result = false;
        }

        if ($result === false && !$this->isTimedOut()) {
            throw new NetworkException(
                'socket writing failed: '
                . socket_strerror(socket_last_error())
            );
        }

        return $result;
    }
}

