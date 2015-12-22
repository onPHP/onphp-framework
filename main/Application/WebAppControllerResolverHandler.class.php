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
class WebAppControllerResolverHandler implements InterceptingChainHandler
{
    const CONTROLLER_POSTFIX = 'Controller';

    protected $defaultController = 'MainController';

    protected $notfoundController = 'NotFoundController';

    /**
     * @return WebAppControllerResolverHandler
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @return WebAppControllerResolverHandler
     */
    public function run(InterceptingChain $chain)
    {
        /* @var $chain WebApplication */
        $request = $chain->getRequest();

        if ($controllerName = $this->getControllerNameByArea($chain)) {
            $chain->setControllerName($controllerName);
        } elseif ($controllerName = $this->getControllerNameByOtherData($chain)) {
            $chain->setControllerName($controllerName);
        } else {
            $chain->setControllerName($this->defaultController);
        }

        $chain->next();

        return $this;
    }

    protected function getControllerNameByArea(InterceptingChain $chain)
    {
        /** @var HttpRequest $request */
        $request = $chain->getRequest();

        $area = null;
        if ($request->hasAttachedVar('area')) {
            $area = $request->getAttachedVar('area');
        } elseif ($request->hasGetVar('area')) {
            $area = $chain->getRequest()->getGetVar('area');
        } elseif ($request->hasPostVar('area')) {
            $area = $chain->getRequest()->getPostVar('area');
        }

        if (
            $area
            && $this->checkControllerName($area . self::CONTROLLER_POSTFIX, $chain->getPathController())
        ) {
            return $area . self::CONTROLLER_POSTFIX;
        } elseif ($area) {
            HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_404));
            return $this->notfoundController;
        }
        return null;
    }

    protected function checkControllerName($controllerName, $path)
    {
        return
            ClassUtils::isClassName($controllerName)
            && $path
            && is_readable($path . $controllerName . EXT_CLASS);
    }

    protected function getControllerNameByOtherData(InterceptingChain $chain)
    {
        return null;
    }

    /**
     * @return WebAppControllerResolverHandler
     */
    public function setDefaultController($defaultController)
    {
        $this->defaultController = $defaultController;

        return $this;
    }

    /**
     * @return WebAppControllerResolverHandler
     */
    public function setNotfoundController($notfoundController)
    {
        $this->notfoundController = $notfoundController;

        return $this;
    }
}
