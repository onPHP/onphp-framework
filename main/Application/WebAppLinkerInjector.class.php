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
class WebAppLinkerInjector implements InterceptingChainHandler
{
    /**
     * @var string
     */
    private $logClassName = null;
    private $baseUrl = null;

    /**
     * @return WebAppLinkerInjector
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param string $logClassName
     * @return WebAppLinkerInjector
     */
    public function setLogClassName($logClassName)
    {
        $this->logClassName = $logClassName;
        return $this;
    }

    /**
     * @param string $baseUrl
     * @return WebAppLinkerInjector
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function run(InterceptingChain $chain)
    {
        /* @var $chain WebApplication */
        $serviceLocator = $chain->getServiceLocator();
        $linker = $serviceLocator->spawn('ToolkitLinkUtils');
        /* @var $linker ToolkitLinkUtils */
        $linker->setLogClassName($this->logClassName)->setBaseUrl($this->baseUrl);
        $serviceLocator->set('linker', $linker);

        $chain->next();
    }
}