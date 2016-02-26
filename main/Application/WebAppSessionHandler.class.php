<?php

/***************************************************************************
 *   Copyright (C) 2009 by Solomatin Alexandr                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class WebAppSessionHandler implements InterceptingChainHandler
{
    protected $sessionName = null;
    protected $cookiePath = null;
    protected $cookieDomain = null;
    protected $cookieTime = 0;


    /**
     * @return WebAppSessionHandler
     */
    public function run(InterceptingChain $chain)
    {
        Assert::isNotEmpty($this->sessionName, 'sessionName must not be empty');

        $sessionName = session_name($this->sessionName);
        session_set_cookie_params(
            $this->cookieTime,
            $this->cookiePath,
            ($this->cookieDomain !== null)
                ? ('.' . $this->cookieDomain)
                : null
        );

        if (
            array_key_exists($sessionName, $_REQUEST)
            && !preg_match('/^[0-9a-z\-]+$/i', $_REQUEST[$sessionName])
        ) {
            unset($_REQUEST[$sessionName]);
        }

        if (
            array_key_exists($sessionName, $_COOKIE)
            && !preg_match('/^[0-9a-z\-]+$/i', $_COOKIE[$sessionName])
        ) {
            unset($_COOKIE[$sessionName]);
        }

        $session = SessionWrapper::me();
        $this->startSessionIfNeed($session, $chain);

        $chain->getServiceLocator()->set('session', $session);

        $chain->next();

        return $this;
    }

    protected function startSessionIfNeed(SessionWrapper $session, WebApplication $chain)
    {
        if (!empty($_COOKIE[session_name()])) {
            $session->start();
        } else {
            /**
             * Not start session if user disable cookies or if it's a bot
             **/
        }
    }

    /**
     * @return WebAppSessionHandler
     */
    public function setSessionName($sessionName)
    {
        $this->sessionName = $sessionName;

        return $this;
    }

    /**
     * @return WebAppSessionHandler
     */
    public function setCookiePath($cookiePath)
    {
        $this->cookiePath = $cookiePath;

        return $this;
    }

    /**
     * @return WebAppSessionHandler
     */
    public function setCookieDomain($cookieDomain)
    {
        $this->cookieDomain = $cookieDomain;

        return $this;
    }

    /**
     * @param int $cookieTime
     * @return WebAppSessionHandler
     */
    public function setCookieTime($cookieTime)
    {
        $this->cookieTime = $cookieTime;
        return $this;
    }
}