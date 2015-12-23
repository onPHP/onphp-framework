<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @see Session
 *
 * @ingroup Base
 **/
class SessionNotStartedException extends BaseException
{
    public function __construct()
    {
        return
            parent::__construct(
                'start session before assign or access session variables'
            );
    }
}

/**
 * Simple static wrapper around session_*() functions.
 *
 * @ingroup Base
 **/
class Session extends StaticFactory
{
    /** @var boolean */
    private static $isStarted = false;

    /**
     * create session
     */
    public static function start()
    {
        session_start();
        self::$isStarted = true;
    }

    /**
     * destroy session
     *
     * @throws SessionNotStartedException
     */
    public static function destroy()
    {
        if (!self::$isStarted) {
            throw new SessionNotStartedException();
        }

        self::$isStarted = false;

        try {
            session_destroy();
        } catch (BaseException $e) {
            // stfu
        }

        setcookie(session_name(), null, 0, '/');
    }

    /**
     * assign var
     *
     * @param $var
     * @param $val
     * @throws SessionNotStartedException
     */
    public static function assign($var, $val)
    {
        if (!self::isStarted()) {
            throw new SessionNotStartedException();
        }

        $_SESSION[$var] = $val;
    }

    /**
     * @return bool
     */
    public static function isStarted()
    {
        return self::$isStarted;
    }

    /**
     * @param array ...$args
     * @return boolean
     * @throws SessionNotStartedException
     * @throws WrongArgumentException
     */
    public static function exist(...$args)
    {
        if (!self::isStarted()) {
            throw new SessionNotStartedException();
        }


        Assert::isNotEmptyArray($args, 'missing argument(s)');

        foreach ($args as $arg) {
            if (!isset($_SESSION[$arg])) {
                return false;
            }
        }

        return true;
    }

    /**
     * get the value for the key
     *
     * @param $var
     * @return null
     * @throws SessionNotStartedException
     */
    public static function get($var)
    {
        if (!self::isStarted()) {
            throw new SessionNotStartedException();
        }

        return isset($_SESSION[$var]) ? $_SESSION[$var] : null;
    }

    /**
     * get all session
     *
     * @return mixed
     */
    public static function &getAll()
    {
        return $_SESSION;
    }

    /**
     * removal of all values
     *
     * @throws SessionNotStartedException
     * @throws WrongArgumentException
     */
    public static function dropAll()
    {
        if (!self::isStarted()) {
            throw new SessionNotStartedException();
        }

        if ($_SESSION) {
            self::drop(array_keys($_SESSION));
        }

    }

    /**
     * Removal of values by index or indices
     *
     * @param array ...$args
     * @throws SessionNotStartedException
     * @throws WrongArgumentException
     */
    public static function drop(...$args)
    {
        if (!self::isStarted()) {
            throw new SessionNotStartedException();
        }

        Assert::isNotEmptyArray($args, 'missing argument(s)');

        foreach ($args as $arg) {
            unset($_SESSION[$arg]);
        }
    }

    /**
     * array assign
     *
     * @param $scope
     * @param $array
     * @throws WrongArgumentException
     */
    public static function arrayAssign($scope, $array)
    {
        Assert::isArray($array);

        foreach ($array as $var) {
            if (isset($scope[$var])) {
                $_SESSION[$var] = $scope[$var];
            }
        }
    }

    /**
     * get session name
     *
     * @return string
     * @throws SessionNotStartedException
     */
    public static function getName() : string
    {
        if (!self::isStarted())
            throw new SessionNotStartedException();

        return session_name();
    }

    /**
     * get session index
     *
     * @return string
     * @throws SessionNotStartedException
     */
    public static function getId() : string
    {
        if (!self::isStarted())
            throw new SessionNotStartedException();

        return session_id();
    }
}
