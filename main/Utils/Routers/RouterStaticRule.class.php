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
class RouterStaticRule extends RouterBaseRule
{
    protected $route = null;

    public function __construct($route)
    {
        // FIXME: rtrim. probably?
        $this->route = trim($route, '/');
    }

    /**
     * @param HttpRequest $request
     * @return array|bool
     * @throws RouterException
     */
    public function match(HttpRequest $request)
    {
        $path = $this->processPath($request)->toString();

        // FIXME: rtrim, probably?
        if (trim(urldecode($path), '/') == $this->route) {
            return $this->defaults;
        }

        return false;
    }

    /**
     * @param array $data
     * @param bool|false $reset
     * @param bool|false $encode
     * @return null|string
     */
    public function assembly(
        array $data = [],
        $reset = false,
        $encode = false
    ) {
        return $this->route;
    }
}

