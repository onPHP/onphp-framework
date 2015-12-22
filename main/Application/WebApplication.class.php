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
class WebApplication extends InterceptingChain implements IServiceLocatorSupport
{
    const OBJ_REQUEST = 'request';
    const OBJ_MAV = 'mav';
    const OBJ_CONTROLLER_NAME = 'controllerName';
    const OBJ_SERVICE_LOCATOR = 'serviceLocator';
    const OBJ_PATH_WEB = 'pathWeb';
    const OBJ_PATH_CONTROLLER = 'pathController';
    const OBJ_PATH_TEMPLATE = 'pathTemplate';
    const OBJ_PATH_TEMPLATE_DEFAULT = 'pathTemplateDefault';

    protected $vars = array();

    public function __construct()
    {
        $request = HttpRequest::create()->
        setGet($_GET)->
        setPost($_POST)->
        setCookie($_COOKIE)->
        setServer($_SERVER)->
        setFiles($_FILES);

        if (!empty($_SESSION)) {
            $request->setSession($_SESSION);
        }

        $this->setRequest($request);

        return $this;
    }

    /**
     * @return WebApplication
     */
    public function setRequest(HttpRequest $request)
    {
        return $this->setVar(self::OBJ_REQUEST, $request);
    }

    /**
     * @param $name
     * @param $var
     * @return $this
     * @throws WrongStateException
     */
    public function setVar($name, $var)
    {
        if ($this->hasVar($name)) {
            throw new WrongStateException("var '$name' already setted");
        }
        $this->vars[$name] = $var;

        return $this;
    }

    public function hasVar($name)
    {
        return array_key_exists($name, $this->vars);
    }

    /**
     * @return WebApplication
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param $name
     * @return $this
     * @throws MissingElementException
     */
    public function dropVar($name)
    {
        if (!$this->hasVar($name)) {
            throw new MissingElementException("not found var '$name'");
        }
        unset($this->vars[$name]);

        return $this;
    }

    /**
     * @return mixed
     * @throws MissingElementException
     */
    public function getRequest()
    {
        return $this->getVar(self::OBJ_REQUEST);
    }

    public function getVar($name)
    {
        if (!$this->hasVar($name)) {
            throw new MissingElementException("not found var '$name'");
        }
        return $this->vars[$name];
    }

    /**
     * @return ModelAndView
     */
    public function getMav()
    {
        return $this->getVar(self::OBJ_MAV);
    }

    /**
     * @return WebApplication
     */
    public function setMav(ModelAndView $mav)
    {
        return $this->setVar(self::OBJ_MAV, $mav);
    }

    public function getControllerName()
    {
        return $this->getVar(self::OBJ_CONTROLLER_NAME);
    }

    /**
     * @return WebApplication
     */
    public function setControllerName($controllerName)
    {
        return $this->setVar(self::OBJ_CONTROLLER_NAME, $controllerName);
    }

    /**
     * @return ServiceLocator
     */
    public function getServiceLocator()
    {
        return $this->getVar(self::OBJ_SERVICE_LOCATOR);
    }

    /**
     * @return WebApplication
     */
    public function setServiceLocator(IServiceLocator $serviceLocator)
    {
        return $this->setVar(self::OBJ_SERVICE_LOCATOR, $serviceLocator);
    }

    public function getPathWeb()
    {
        return $this->getVar(self::OBJ_PATH_WEB);
    }

    /**
     * @return WebApplication
     */
    public function setPathWeb($pathWeb)
    {
        return $this->setVar(self::OBJ_PATH_WEB, $pathWeb);
    }

    public function getPathController()
    {
        return $this->getVar(self::OBJ_PATH_CONTROLLER);
    }

    /**
     * @return WebApplication
     */
    public function setPathController($pathController)
    {
        return $this->setVar(self::OBJ_PATH_CONTROLLER, $pathController);
    }

    public function getPathTemplate()
    {
        return $this->getVar(self::OBJ_PATH_TEMPLATE);
    }

    /**
     * @return WebApplication
     */
    public function setPathTemplate($pathTemplate)
    {
        return $this->setVar(self::OBJ_PATH_TEMPLATE, $pathTemplate);
    }

    public function getPathTemplateDefault()
    {
        return $this->getVar(self::OBJ_PATH_TEMPLATE_DEFAULT);
    }

    /**
     * @return WebApplication
     */
    public function setPathTemplateDefault($pathTemplateDefault)
    {
        return $this->setVar(self::OBJ_PATH_TEMPLATE_DEFAULT, $pathTemplateDefault);
    }
}