<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Flow
 **/
class HttpRequest
{
    // contains all variables from $_GET
    private $get = [];

    // from $_POST
    private $post = [];

    // guess what
    private $server = [];

    // fortune one
    private $cookie = [];

    // reference, not copy
    private $session = [];

    // uploads and downloads (CurlHttpClient)
    private $files = [];

    // all other sh1t
    private $attached = [];

    private $headers = null;

    /**
     * @var HttpMethod
     */
    private $method = null;

    /**
     * @var HttpUrl
     */
    private $url = null;

    private $body = null;

    public function __construct()
    {
        $this->headers = new HttpHeaderCollection();
    }

    /**
     * @return HttpRequest
     **/
    public static function createFromGlobals()
    {
        $request =
            (new HttpRequest())
                ->setGet($_GET)
                ->setPost($_POST)
                ->setServer($_SERVER)
                ->setCookie($_COOKIE)
                ->setFiles($_FILES);

        if (isset($_SESSION)) {
            $request->setSession($_SESSION);
        }

        foreach ($_SERVER as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $name = str_replace('_', '-', substr($name, 5));
                $request->setHeaderVar($name, $value);
            }
        }

        if (
            $request->hasServerVar('CONTENT_TYPE')
            && $request->getServerVar('CONTENT_TYPE') !== 'application/x-www-form-urlencoded'
        ) {
            $request->setBody(file_get_contents('php://input'));
        }

        $request->setMethod(
            HttpMethod::createByName($request->getServerVar('REQUEST_METHOD'))
        );

        return $request;
    }

    /**
     * @return HttpRequest
     **/
    public function setHeaderVar($name, $var)
    {
        $this->headers->set($name, $var);
        return $this;
    }

    public function hasServerVar($name)
    {
        return isset($this->server[$name]);
    }

    public function getServerVar($name)
    {
        return $this->server[$name];
    }

    public function &getGet()
    {
        return $this->get;
    }

    /**
     * @return HttpRequest
     **/
    public function setGet(array $get)
    {
        $this->get = $get;

        return $this;
    }

    public function getGetVar($name)
    {
        return $this->get[$name];
    }

    public function hasGetVar($name)
    {
        return isset($this->get[$name]);
    }

    /**
     * @return HttpRequest
     **/
    public function setGetVar($name, $value)
    {
        $this->get[$name] = $value;
        return $this;
    }

    public function &getPost()
    {
        return $this->post;
    }

    /**
     * @return HttpRequest
     **/
    public function setPost(array $post)
    {
        $this->post = $post;

        return $this;
    }

    public function getPostVar($name)
    {
        return $this->post[$name];
    }

    public function hasPostVar($name)
    {
        return isset($this->post[$name]);
    }

    /**
     * @return HttpRequest
     **/
    public function setPostVar($name, $value)
    {
        $this->post[$name] = $value;
        return $this;
    }

    public function &getServer()
    {
        return $this->server;
    }

    /**
     * @return HttpRequest
     **/
    public function setServer(array $server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return HttpRequest
     **/
    public function setServerVar($name, $value)
    {
        $this->server[$name] = $value;
        return $this;
    }

    public function &getCookie()
    {
        return $this->cookie;
    }

    /**
     * @return HttpRequest
     **/
    public function setCookie(array $cookie)
    {
        $this->cookie = $cookie;

        return $this;
    }

    public function getCookieVar($name)
    {
        return $this->cookie[$name];
    }

    public function hasCookieVar($name)
    {
        return isset($this->cookie[$name]);
    }

    public function &getSession()
    {
        return $this->session;
    }

    /**
     * @return HttpRequest
     **/
    public function setSession(array &$session)
    {
        $this->session = &$session;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getSessionVar($name)
    {
        return $this->session[$name];
    }

    public function hasSessionVar($name)
    {
        return isset($this->session[$name]);
    }

    public function &getFiles()
    {
        return $this->files;
    }

    /**
     * @return HttpRequest
     **/
    public function setFiles(array $files)
    {
        $this->files = $files;

        return $this;
    }

    public function getFilesVar($name)
    {
        return $this->files[$name];
    }

    public function hasFilesVar($name)
    {
        return isset($this->files[$name]);
    }

    /**
     * @return HttpRequest
     **/
    public function setAttachedVar($name, $var)
    {
        $this->attached[$name] = $var;

        return $this;
    }

    public function &getAttached()
    {
        return $this->attached;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getAttachedVar($name)
    {
        return $this->attached[$name];
    }

    /**
     * @return HttpRequest
     **/
    public function unsetAttachedVar($name)
    {
        unset($this->attached[$name]);

        return $this;
    }

    public function hasAttachedVar($name)
    {
        return isset($this->attached[$name]);
    }

    public function getByType(RequestType $type)
    {
        return $this->{$type->getName()};
    }

    public function getHeaderList()
    {
        return $this->headers->getAll();
    }

    public function hasHeaderVar($name)
    {
        return isset($this->headers[$name]);
    }

    public function getHeaderVar($name)
    {
        return $this->headers->get($name);
    }

    /**
     * @return HttpRequest
     **/
    public function unsetHeaderVar($name)
    {
        unset($this->headers[$name]);
        return $this;
    }

    /**
     * @return HttpRequest
     **/
    public function setHeaders(array $headers)
    {
        $this->headers = new HttpHeaderCollection($headers);
        return $this;
    }

    /**
     * @return HttpMethod
     **/
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return HttpRequest
     **/
    public function setMethod(HttpMethod $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return HttpUrl
     **/
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return HttpRequest
     **/
    public function setUrl(HttpUrl $url)
    {
        $this->url = $url;
        return $this;
    }

    public function hasBody()
    {
        return $this->body !== null;
    }

    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return HttpRequest
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
}