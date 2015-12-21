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

/**
 * Interface of dynamic wrapper around session_*() functions.
 **/
interface ISessionWrapper
{
    /**
     * @return void
     */
    public function start();

    /**
     * @throws SessionWrapperNotStartedException
     * @return void
     **/
    public function destroy();

    /**
     * @return void
     **/
    public function flush();

    /**
     * @throws SessionWrapperNotStartedException
     * @return void
     **/
    public function assign($var, $val);

    /**
     * @throws WrongArgumentException
     * @throws SessionWrapperNotStartedException
     * @return boolean
     **/
    public function exist(/* ... */);

    /**
     * @throws SessionWrapperNotStartedException
     * @return any
     **/
    public function get($var);

    /**
     * @throws SessionWrapperNotStartedException
     * @return array
     **/
    public function &getAll();

    /**
     * @throws WrongArgumentException
     * @throws SessionWrapperNotStartedException
     * @return void
     **/
    public function drop(/* ... */);

    /**
     * @throws SessionWrapperNotStartedException
     * @return void
     **/
    public function dropAll();

    /**
     * @return boolean
     */
    public function isStarted();

    /**
     * assigns to $_SESSION scope variables defined in given array
     * @return void
     **/
    public function arrayAssign($scope, $array);

    /**
     * @throws SessionWrapperNotStartedException
     * @return string
     **/
    public function getName();

    /**
     * @throws SessionWrapperNotStartedException
     * @return string
     **/
    public function getId();
}
