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
class MultiPrefixPhpViewResolverParametrized extends MultiPrefixPhpViewResolver
{

    protected $params = array();

    /**
     * @return MultiPrefixPhpViewResolverParametrized
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @param $name
     * @return mixed
     * @throws MissingElementException
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new MissingElementException("not setted value with name '$name'");
        }
        return $this->params[$name];
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        Assert::isScalar($name);
        return array_key_exists($name, $this->params);
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     * @throws WrongStateException
     */
    public function set($name, $value)
    {
        if ($this->has($name)) {
            throw new WrongStateException("value with name '$name' already setted ");
        }
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     * @throws MissingElementException
     */
    public function drop($name)
    {
        if (!$this->has($name)) {
            throw new MissingElementException("not setted value with name '$name'");
        }
        unset($this->params[$name]);
        return $this;
    }

    /**
     * @return View
     **/
    protected function makeView($prefix, $viewName)
    {
        $view = parent::makeView($prefix, $viewName);
        if ($view instanceof SimplePhpViewParametrized) {
            foreach ($this->params as $key => $value) {
                $view->set($key, $value);
            }
        }
        return $view;
    }
}
