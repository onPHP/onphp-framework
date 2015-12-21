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
class SimplePhpViewParametrized extends CustomPhpView
{
    /**
     * @var Model
     */
    protected $model = null;
    protected $params = array();

    public function render($model = null)
    {
        $this->model = $model;
        return parent::render($model);
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
     * @param $name
     * @return bool
     * @throws WrongArgumentException
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
     * @param $templateName
     * @param array $params
     */
    protected function template($templateName, array $params = array())
    {
        if (!empty($params)) {
            $model = Model::create()->merge($this->model);
            foreach ($params as $paramName => $paramValue) {
                $model->set($paramName, $paramValue);
            }
            $this->partViewer->view($templateName, $model);
        } else {
            $this->partViewer->view($templateName);
        }
    }

    /**
     * @param $templateName
     * @param null $model
     * @throws WrongArgumentException
     */
    protected function view($templateName, /* Model */
                            $model = null)
    {
        if ($model && is_array($model)) {
            $model = $this->array2Model($model);
        } elseif ($model) {
            Assert::isInstance($model, 'Model', '$model must be instance of Model or array or null');
        }
        $this->partViewer->view($templateName, $model);
    }

    /**
     * @param array $array
     * @return Model
     */
    private function array2Model(array $array)
    {
        $model = Model::create();
        foreach ($array as $key => $value) {
            $model->set($key, $value);
        }

        return $model;
    }

    /**
     * @param $value
     * @return string
     */
    protected function escape($value/*,  sprintf params */)
    {
        if (func_num_args() > 1) {
            $value = call_user_func_array('sprintf', func_get_args());
        }
        return htmlspecialchars($value);
    }
}