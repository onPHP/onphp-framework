<?php
/***************************************************************************
 *   Copyright (C) 2013 by Alexander A. Klestoff                           *
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
class SimpleFrontController implements Controller
{
    const DEFAULT_CONTROLLER = 'main';
    const DEFAULT_TEMPLATE = 'main';
    const DEFAULT_ACTION = 'show';
    //LIKE /controller/42/action.html
    const ROUTE_REGEXP = '(\w+)?((/(\d+))?(/(\w+)))?(\.(.*))?';

    const DEFAULT_ROUTE_NAME = '*';

    const DEFAULT_FORMAT = 'html';


    protected $allowedFormatList = [self::DEFAULT_FORMAT];

    /**
     * @var HttpRequest
     */
    protected $request = null;
    private $controllerName = null;

    private $templatesDirectory = null;

    public function __construct($templatesDirectory)
    {
        $this->templatesDirectory = $templatesDirectory;
    }

    /**
     * @return SimpleFrontController
     */
    public static function create($templatesDirectory)
    {
        return new static($templatesDirectory);
    }

    public function handleRequest(HttpRequest $request)
    {
        $this->request = $request;

        $this->getRouter()->route($request);

        $this->prepareResponseFormat($request);

        $this->handleMav(
            $this->
            makeControllerChain()->
            handleRequest($request)
        );
    }

    /**
     * @return Router
     */
    protected function getRouter()
    {
        return
            RouterRewrite::me()->
            addRoute(
                self::DEFAULT_ROUTE_NAME,
                (new RouterRegexpRule(self::ROUTE_REGEXP))
                    ->setMap(
                        [
                            1 => 'area',
                            4 => 'id',
                            6 => 'action',
                            8 => 'format',
                        ]
                    )
                    ->setDefaults(
                        [
                            'area' => self::DEFAULT_CONTROLLER,
                            'action' => self::DEFAULT_ACTION,
                            'id' => 0,
                            'format' => self::DEFAULT_FORMAT
                        ]
                    )
            );
    }

    protected function prepareResponseFormat()
    {
        if ($this->request->hasAttachedVar('format')) {
            Assert::isNotFalse(
                array_search(
                    $this->request->getAttachedVar('format'),
                    $this->allowedFormatList
                )
            );

        } else {
            $this->request->setAttachedVar('format', self::DEFAULT_FORMAT);
        }
    }

    protected function handleMav(ModelAndView $mav)
    {
        $view = $mav->getView() ?: self::DEFAULT_TEMPLATE;
        $model = $mav->getModel();

        if (!$view instanceof RedirectView) {
            $model->set('area', $this->controllerName);
        }

        if (is_string($view)) {
            if ($view == $this->controllerName) {
                $view = self::DEFAULT_TEMPLATE;
            }

            $viewResolver = $this->getViewResolver();

            foreach ($this->getTemplatePathList() as $templatePath) {
                $viewResolver->addPrefix($templatePath);
            }

            $view = $viewResolver->resolveViewName($view);
        }

        $view->render($model);
    }

    protected function getViewResolver()
    {
        return
            (new MultiPrefixPhpViewResolver())
                ->setViewClassName('SimplePhpView');
    }

    protected function getTemplatePathList()
    {
        return
            [
                $this->templatesDirectory . $this->request->getAttachedVar('format') . '/' . $this->controllerName . '/',
                $this->templatesDirectory . $this->request->getAttachedVar('format') . '/'
            ];
    }

    /**
     * @return Controller
     */
    protected function makeControllerChain()
    {
        $this->controllerName = self::DEFAULT_CONTROLLER;

        if (
            $this->request->hasAttachedVar('area')
            && $this->request->getAttachedVar('area')
            && ClassUtils::isClassName(
                $this->request->getAttachedVar('area')
            )
        ) {
            $this->controllerName = $this->request->getAttachedVar('area');
        }

        return new $this->controllerName;
    }
}