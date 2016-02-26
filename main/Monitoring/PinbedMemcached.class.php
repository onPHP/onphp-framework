<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 *
 **/
class PinbedMemcached extends SocketMemcached
{
    public function __construct(
        $host = SocketMemcached::DEFAULT_HOST,
        $port = SocketMemcached::DEFAULT_PORT,
        $buffer = SocketMemcached::DEFAULT_BUFFER
    ) {
        if (PinbaClient::isEnabled()) {
            PinbaClient::me()->timerStart(
                'memcached_' . $host . '_' . $port . '_connect',
                ['memcached_connect' => $host . '_' . $port]
            );
        }

        parent::__construct($host, $port, $buffer);

        if (PinbaClient::isEnabled()) {
            PinbaClient::me()->timerStop(
                'memcached_' . $host . '_' . $port . '_connect'
            );
        }
    }
}

