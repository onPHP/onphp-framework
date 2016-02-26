<?php

/***************************************************************************
 *   Copyright (C) 2009 by Solomatin Alexandr                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class WebAppViewHandler implements InterceptingChainHandler
{
    /**
     * Templater name
     * @var string
     */
    const VIEW_CLASS_NAME_DEFAULT = 'SimplePhpView';

    /**
     * @var array
     */
    private $headers = [];


    /**
     * @param InterceptingChain $chain
     * @return $this
     */
    public function run(InterceptingChain $chain)
    {
        $view = $chain->getMav()->getView();
        $model = $chain->getMav()->getModel();

        if (!$view instanceof View) {
            $viewName = $view;
            $viewResolver = $this->getViewResolver($chain, $model);
            $view = $viewResolver->resolveViewName($viewName);
        }

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        if ($chain->getMav()->viewIsNormal()) {
            $this->updateNonRedirectModel($chain, $model);
        }
        $view->render($model);

        $chain->next();

        return $this;
    }

    /**
     * @param InterceptingChain $chain
     * @param Model $model
     * @return PhpViewResolver
     */
    protected function getViewResolver(InterceptingChain $chain, Model $model)
    {
        return new PhpViewResolver($chain->getPathTemplateDefault(), EXT_TPL);
    }

    /**
     * @param InterceptingChain $chain
     * @param Model $model
     * @return $this
     */
    protected function updateNonRedirectModel(InterceptingChain $chain, Model $model)
    {
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * @return string
     */
    protected function getViewClassName()
    {
        return self::VIEW_CLASS_NAME_DEFAULT;
    }
}

