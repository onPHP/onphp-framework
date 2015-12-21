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
class PhpViewResolverParametrized implements ViewResolver
{

    protected $params = array();

    private $prefix = null;
    private $postfix = null;

    public function __construct($prefix = null, $postfix = null)
    {
        $this->prefix = $prefix;
        $this->postfix = $postfix;
    }

    /**
     * @return PhpViewResolverParametrized
     **/
    public static function create($prefix = null, $postfix = null)
    {
        return new self($prefix, $postfix);
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return PhpViewResolverParametrized
     **/
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPostfix()
    {
        return $this->postfix;
    }

    /**
     * @return PhpViewResolverParametrized
     **/
    public function setPostfix($postfix)
    {
        $this->postfix = $postfix;

        return $this;
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
     * @param type $name
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
     * @return SimplePhpViewParametrized
     **/
    public function resolveViewName($viewName)
    {
        $view = new SimplePhpViewParametrized(
            $this->prefix . $viewName . $this->postfix,
            $this
        );
        foreach ($this->params as $name => $value) {
            $view->set($name, $value);
        }
        return $view;
    }

    /**
     * @param $viewName
     * @return bool
     */
    public function viewExists($viewName)
    {
        return is_readable($this->prefix . $viewName . $this->postfix);
    }
}