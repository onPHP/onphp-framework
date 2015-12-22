<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
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
class Model implements SimplifiedArrayAccess
{
    private $vars = array();

    /**
     * @return Model
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return Model
     **/
    public function clean()
    {
        $this->vars = array();

        return $this;
    }

    public function get($name)
    {
        if (!$this->has($name))
            throw new MissingElementException('Unknown var "' . $name . '"');

        return $this->vars[$name];
    }

    public function has($name)
    {
        return isset($this->vars[$name]);
    }

    /**
     * @return Model
     **/
    public function drop($name)
    {
        unset($this->vars[$name]);

        return $this;
    }

    /**
     * @return Model
     **/
    public function merge(Model $model, $overwrite = false)
    {
        if (!$model->isEmpty()) {

            $vars = $model->getList();
            foreach ($vars as $name => $value) {
                if (!$overwrite && $this->has($name))
                    continue;
                $this->set($name, $value);
            }

        }

        return $this;
    }

    public function isEmpty()
    {
        return ($this->vars === array());
    }

    public function getList()
    {
        return $this->vars;
    }

    /**
     * @return Model
     **/
    public function set($name, $var)
    {
        $this->vars[$name] = $var;

        return $this;
    }
}