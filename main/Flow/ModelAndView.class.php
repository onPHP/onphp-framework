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
class ModelAndView
{
    private $model = null;

    private $view = null;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * @return ModelAndView
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return Model
     **/
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return ModelAndView
     **/
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    public function getView()
    {
        return $this->view;
    }

    /**
     * @return ModelAndView
     **/
    public function setView($view)
    {
        Assert::isTrue(
            ($view instanceof View) || is_string($view),
            'do not know, what to do with such view'
        );

        $this->view = $view;

        return $this;
    }

    public function dropView()
    {
        $this->view = null;

        return $this;
    }

    public function viewIsNormal()
    {
        return (
            !$this->viewIsRedirect()
            && $this->view !== View::ERROR_VIEW
        );
    }

    public function viewIsRedirect()
    {
        return
            ($this->view instanceof CleanRedirectView)
            || (
                is_string($this->view)
                && strpos($this->view, 'redirect') === 0
            );
    }
}