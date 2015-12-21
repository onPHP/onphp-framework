<?php
/***************************************************************************
 *   Copyright (C) 2011-2012 by Aleksey S. Denisov                         *
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
class CustomPhpView extends EmptyView
{
    protected $templatePath = null;
    protected $partViewResolver = null;

    /**
     * @var PartViewer
     */
    protected $partViewer = null;

    public function __construct($templatePath, ViewResolver $partViewResolver)
    {
        $this->templatePath = $templatePath;
        $this->partViewResolver = $partViewResolver;
    }

    public function toString($model = null)
    {
        ob_start();
        try {
            $this->render($model);
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
    }

    /**
     * @return SimplePhpView
     **/
    public function render($model = null)
    {
        /**@var Model $model */

        Assert::isTrue($model === null || $model instanceof Model);

        if ($model)
            extract($model->getList());

        $partViewer = new PartViewer($this->partViewResolver, $model);

        $this->preRender($partViewer);

        include $this->templatePath;

        $this->postRender($partViewer);

        return $this;
    }

    /**
     * @return SimplePhpView
     **/
    protected function preRender(PartViewer $partViewer)
    {
        $this->partViewer = $partViewer;
        return $this;
    }

    /**
     * @return SimplePhpView
     **/
    protected function postRender(PartViewer $partViewer)
    {
        $this->partViewer = null;
        return $this;
    }
}

