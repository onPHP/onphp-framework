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
class RouterChainRule extends RouterBaseRule
{
    protected $routes = [];
    protected $separators = [];

    /**
     * @deprecated
     * @return RouterChainRule
     **/
    public static function create()
    {
        return new self();
    }

    /**
     * @return RouterChainRule
     **/
    public function chain(RouterRule $route, $separator = '/')
    {
        $this->routes[] = $route;
        $this->separators[] = $separator;

        return $this;
    }

    public function getCount()
    {
        return count($this->routes);
    }

    public function match(HttpRequest $request)
    {
        $values = [];

        foreach ($this->routes as $key => $route) {
            $res = $route->match($request);

            if (empty($res)) {
                return [];
            }

            $values = $res + $values;
        }

        return $values;
    }

    public function assembly(
        array $data = [],
        $reset = false,
        $encode = false
    ) {
        $value = null;

        foreach ($this->routes as $key => $route) {
            if ($key > 0) {
                $value .= $this->separators[$key];
            }

            $value .= $route->assembly($data, $reset, $encode);

            if (
                $route instanceof RouterHostnameRule
                && $key > 0
            ) {
                throw new RouterException('wrong chain route');
            }
        }

        return $value;
    }
}

