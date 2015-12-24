<?php

/***************************************************************************
 *   Copyright (C) 2011 by Alexey Denisov                                  *
 *   alexeydsov@gmail.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class WebAppControllerMultiResolverHandler extends WebAppControllerResolverHandler
{
    protected $subPathList = array();

    /**
     * @deprecated
     *
     * @return WebAppControllerMultiResolverHandler
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param $subPath
     * @return $this
     */
    public function addSubPath($subPath)
    {
        $this->subPathList[] = $subPath;
        return $this;
    }

    /**
     * @param $controllerName
     * @param $path
     * @return bool
     */
    protected function checkControllerName($controllerName, $path)
    {
        return
            ClassUtils::isClassName($controllerName)
            && $path
            && $this->isReadable($controllerName, $path);
    }

    /**
     * @param $controllerName
     * @param $path
     * @return bool
     */
    protected function isReadable($controllerName, $path)
    {
        $subPathList = $this->subPathList;
        array_unshift($subPathList, '');

        foreach ($subPathList as $subPath) {
            if (is_readable($path . $subPath . $controllerName . EXT_CLASS)) {
                return true;
            }
        }

        return false;
    }
}