<?php

/***************************************************************************
 *   Copyright (C) 2008 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
final class RouterRewrite extends Singleton implements Router, Instantiatable
{
    protected $routes = [];
    protected $currentRoute = null;

    /**
     * @var HttpRequest
     **/
    protected $request = null;

    /**
     * @var HttpUrl
     **/
    protected $baseUrl = null;

    protected function __construct()
    {
        $this->baseUrl = new HttpUrl();
    }

    /**
     * @return RouterRewrite
     **/
    public static function me()
    {
        return self::getInstance(__CLASS__);
    }

    /**
     * @return HttpRequest
     **/
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return RouterRewrite
     **/
    public function setRequest(HttpRequest $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return RouterRewrite
     **/
    public function addRoutes(array $routes)
    {
        foreach ($routes as $name => $route) {
            $this->addRoute($name, $route);
        }

        return $this;
    }

    /**
     * @return RouterRewrite
     **/
    public function addRoute($name, RouterRule $route)
    {
        if ($this->hasRoute($name)) {
            throw new RouterException(
                "Route with name '{$name}' is already defined"
            );
        }

        $this->routes[$name] = $route;

        return $this;
    }

    /**
     * @return boolean
     **/
    public function hasRoute($name)
    {
        return isset($this->routes[$name]);
    }

    /**
     * @throws RouterException
     * @return RouterRewrite
     **/
    public function removeRoute($name)
    {
        if (!$this->hasRoute($name)) {
            throw new RouterException(
                "Route '{$name}' is not defined"
            );
        }

        unset($this->routes[$name]);

        return $this;
    }

    /**
     * @throws RouterException
     * @return RouterRule
     **/
    public function getCurrentRoute()
    {
        if (!isset($this->currentRoute)) {
            throw new RouterException(
                "Current route is not defined"
            );
        }

        return $this->getRoute($this->currentRoute);
    }

    /**
     * @throws RouterException
     * @return RouterRule
     **/
    public function getRoute($name)
    {
        if (!$this->hasRoute($name)) {
            throw new RouterException(
                "Route '{$name}' is not defined"
            );
        }

        return $this->routes[$name];
    }

    /**
     * @return array
     **/
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Find a matching route to the current REQUEST_URI and
     * inject returning values to the HttpRequest object.
     *
     * @return HttpRequest
     **/
    public function route(HttpRequest $request)
    {
        $this->setRequest($request);

        foreach (array_reverse($this->routes) as $name => $route) {
            if ($params = $route->match($request)) {
                $this->setRequestParams($request, $params);
                $this->currentRoute = $name;

                break;
            }
        }

        return $request;
    }

    /**
     * @return RouterRewrite
     **/
    protected function setRequestParams(HttpRequest $request, array $params)
    {
        foreach ($params as $param => $value) {
            $request->setAttachedVar($param, $value);
        }

        return $this;
    }

    /**
     * @throws RouterException
     * @return string
     **/
    public function assembly(
        array $userParams = [],
        $name = null,
        $reset = false,
        $encode = true
    ) {
        if ($name === null) {
            $name = $this->getCurrentRouteName();
        }

        $route = $this->getRoute($name);
        $url = $route->assembly($userParams, $reset, $encode);

        if (!preg_match('|^[a-z]+://|', $url)) {
            if ($this->getBaseUrl()) {
                $url = rtrim($this->getBaseUrl()->toString(), '/') . '/' . $url;
            } else {
                $url = '/' . $url;
            }
        }

        return $url;
    }

    /**
     * @throws RouterException
     * @return RouterRule
     **/
    public function getCurrentRouteName()
    {
        if (!isset($this->currentRoute)) {
            throw new RouterException(
                "Current route is not defined"
            );
        }

        return $this->currentRoute;
    }

    /**
     * @return HttpUrl
     **/
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return RouterRewrite
     **/
    public function setBaseUrl(HttpUrl $url)
    {
        $this->baseUrl = $url;

        return $this;
    }

    /**
     * @return RouterRewrite
     **/
    public function resetAll()
    {
        return $this->
        resetBaseUrl()->
        resetRequest()->
        resetRoutes();

        return $this;
    }

    /**
     * @return RouterRule
     **/
    public function resetRoutes()
    {
        $this->currentRoute = null;
        $this->routes = [];

        return $this;
    }

    /**
     * @return RouterRewrite
     **/
    public function resetRequest()
    {
        $this->request = null;

        return $this;
    }

    /**
     * @return RouterRewrite
     **/
    public function resetBaseUrl()
    {
        $this->baseUrl = null;

        return $this;
    }
}

?>