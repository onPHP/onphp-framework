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
    private static $isStarted = false;

    /**
     * Открытие сессии
     */
    public static function start()
    {
        session_start();
        self::$isStarted = true;
    }

    /**
     * Удаление сессии
     *
     * @throws SessionNotStartedException
     */
    public static function destroy()
    {
        if (!self::$isStarted)
            throw new SessionNotStartedException();

        self::$isStarted = false;

        try {
            session_destroy();
        } catch (BaseException $e) {
            // stfu
        }

        setcookie(session_name(), null, 0, '/');
    }

    /**
     * Добавить в сессию
     *
     * @param $var
     * @param $val
     * @throws SessionNotStartedException
     */
    public static function assign($var, $val)
    {
        if (!self::isStarted())
            throw new SessionNotStartedException();

        $_SESSION[$var] = $val;
    }

    /**
     * @return bool
     * @throws SessionNotStartedException
     * @throws WrongArgumentException
     */
    public static function exist(/* ... */)
    {
        if (!self::isStarted())
            throw new SessionNotStartedException();

        if (!func_num_args())
            throw new WrongArgumentException('missing argument(s)');

        foreach (func_get_args() as $arg) {
            if (!isset($_SESSION[$arg]))
                return false;
        }

        return true;
    }

    /**
     * Получить значение из сессии по ключу
     *
     * @param $var
     * @return null
     * @throws SessionNotStartedException
     */
    public static function get($var)
    {
        if (!self::isStarted())
            throw new SessionNotStartedException();

        return isset($_SESSION[$var]) ? $_SESSION[$var] : null;
    }

    /**
     * @return mixed
     */
    public static function &getAll()
    {
        return $_SESSION;
    }

    /**
     * Удаление из сессии или удаление значения по ключу
     *
     * @throws SessionNotStartedException
     * @throws WrongArgumentException
     */
    public static function drop(/* ... */)
    {
        if (!self::isStarted())
            throw new SessionNotStartedException();

        if (!func_num_args())
            throw new WrongArgumentException('missing argument(s)');

        foreach (func_get_args() as $arg)
            unset($_SESSION[$arg]);
    }

    /**
     * Удаление всех значений сессии
     *
     * @throws SessionNotStartedException
     * @throws WrongArgumentException
     */
    public static function dropAll()
    {
        if (!self::isStarted())
            throw new SessionNotStartedException();

        if ($_SESSION) {
            foreach (array_keys($_SESSION) as $key) {
                self::drop($key);
            }
        }
    }


    public static function isStarted()
    {
        return self::$isStarted;
    }

    /**
     * assigns to $_SESSION scope variables defined in given array
     **/
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
     * @throws SessionNotStartedException
     **/
    public static function getName()
    {
        if (!self::isStarted())
            throw new SessionNotStartedException();

        return session_name();
    }

    /**
     * @throws SessionNotStartedException
     **/
    public static function getId()
    {
        if (!self::isStarted())
            throw new SessionNotStartedException();

        return session_id();
    }
}
