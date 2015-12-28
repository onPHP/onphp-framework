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
trait TServiceLocatorSupport
{

    /**
     * @var ServiceLocator
     */
    protected $serviceLocator = null;

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param IServiceLocator $serviceLocator
     * @return TServiceLocatorSupport
     */
    public function setServiceLocator(IServiceLocator $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }
}

