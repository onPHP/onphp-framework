<?php
/****************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * @ingroup Flow
 **/
abstract class MethodMappedController implements Controller
{
    private $methodMap = array();
    private $defaultAction = null;

    /**
     * @return ModelAndView
     **/
    public function handleRequest(HttpRequest $request)
    {
        if ($action = $this->chooseAction($request)) {

            $method = $this->methodMap[$action];
            $mav = $this->{$method}($request);

            if ($mav->viewIsRedirect())
                return $mav;

            $mav->getModel()->set('action', $action);

            return $mav;

        } else
            return ModelAndView::create();

        Assert::isUnreachable();
    }

    public function chooseAction(HttpRequest $request)
    {
        $action = Primitive::choice('action')->setList($this->methodMap);

        if ($this->getDefaultAction())
            $action->setDefault($this->getDefaultAction());

        Form::create()
            ->add($action)
            ->import($request->getGet())
            ->importMore($request->getPost())
            ->importMore($request->getAttached());

        if (!$command = $action->getValue())
            return $action->getDefault();

        return $command;
    }

    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    /**
     * @return MethodMappedController
     **/
    public function setDefaultAction($action)
    {
        $this->defaultAction = $action;

        return $this;
    }

    /**
     * @return MethodMappedController
     **/
    public function dropMethodMapping($action)
    {
        unset($this->methodMap[$action]);

        return $this;
    }

    public function getMethodMapping()
    {
        return $this->methodMap;
    }

    /**
     * @return MethodMappedController
     **/
    public function setMethodMappingList($array)
    {
        foreach ($array as $action => $methodName)
            $this->setMethodMapping($action, $methodName);

        return $this;
    }

    /**
     * @return MethodMappedController
     **/
    public function setMethodMapping($action, $methodName)
    {
        $this->methodMap[$action] = $methodName;
        return $this;
    }
}