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
class WebAppControllerHandler implements InterceptingChainHandler
{
    /**
     * @return WebAppControllerHandler
     */
    public function run(InterceptingChain $chain)
    {
        $controller = $this->getController($chain);

        $modelAndView = $this->handleRequest($chain, $controller);
        $this->prepairModelAndView($chain, $modelAndView);

        $chain->setMav($modelAndView);

        $chain->next();

        return $this;
    }

    /**
     * По параметрам из chain создаем контроллер
     * @param InterceptingChain $chain
     * @return Controller
     */
    protected function getController(InterceptingChain $chain)
    {
        $controllerName = $chain->getControllerName();
        return $chain->getServiceLocator()->spawn($controllerName);
    }

    /**
     * @return ModelAndView
     */
    protected function handleRequest(InterceptingChain $chain, Controller $controller)
    {
        $modelAndView = $controller->handleRequest($chain->getRequest());

        if (!$modelAndView instanceof ModelAndView) {
            throw new WrongStateException(
                'Controller \'' . get_class($controller) . '\' instead ModelAndView return null'
            );
        }

        return $modelAndView;
    }

    /**
     * @return WebAppControllerHandler
     */
    protected function prepairModelAndView(InterceptingChain $chain, ModelAndView $modelAndView)
    {
        $controllerName = $chain->getControllerName();

        if (!$modelAndView->getView()) {
            $modelAndView->setView($controllerName);
        }

        return $this;
    }
}
