<?php

/***************************************************************************
 *   Copyright (C) 2011 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
class AMQPCredentials
{
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT = '5672';
    const DEFAULT_LOGIN = 'guest';
    const DEFAULT_PASSWORD = 'guest';
    const DEFAULT_VHOST = '/';

    protected $host = null;
    protected $port = null;
    protected $virtualHost = null;
    protected $login = null;
    protected $password = null;


    /**
     * @return AMQPCredentials
     **/
    public static function createDefault()
    {
        return
            (new self())
                ->setHost(self::DEFAULT_HOST)
                ->setPort(self::DEFAULT_PORT)
                ->setLogin(self::DEFAULT_LOGIN)
                ->setPassword(self::DEFAULT_PASSWORD)
                ->setVirtualHost(self::DEFAULT_VHOST);
    }

    /**
     * @return null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return AMQPCredentials
     **/
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return AMQPCredentials
     **/
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return null
     */
    public function getVirtualHost()
    {
        return $this->virtualHost;
    }

    /**
     * @return AMQPCredentials
     **/
    public function setVirtualHost($virtualHost)
    {
        $this->virtualHost = $virtualHost;

        return $this;
    }

    /**
     * @return null
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return AMQPCredentials
     **/
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return AMQPCredentials
     **/
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
}

