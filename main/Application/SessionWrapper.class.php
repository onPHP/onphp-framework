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
 * Dynamic wrapper around session_*() functions.
 **/
class SessionWrapper extends Singleton implements ISessionWrapper
{
    private $isStarted = false;

    /**
     * @return SessionWrapper
     */
    public static function me()
    {
        return self::getInstance(__CLASS__);
    }

    public function start()
    {
        session_start();
        $this->isStarted = true;
    }

    /**
     * @throws SessionWrapperNotStartedException
     **/
    /* void */
    public function destroy()
    {
        if (!$this->isStarted)
            throw new SessionWrapperNotStartedException();

        $this->isStarted = false;

        try {
            session_destroy();
        } catch (BaseException $e) {
            // stfu
        }

        setcookie(session_name(), null, 0, '/');
    }

    public function flush()
    {
        return session_unset();
    }

    /**
     * @throws SessionWrapperNotStartedException
     **/
    /* void */
    public function assign($var, $val)
    {
        if (!self::isStarted())
            throw new SessionWrapperNotStartedException();

        $_SESSION[$var] = $val;
    }

    public function isStarted()
    {
        return $this->isStarted;
    }

    /**
     * @throws WrongArgumentException
     * @throws SessionWrapperNotStartedException
     **/
    public function exist(/* ... */)
    {
        if (!self::isStarted())
            throw new SessionWrapperNotStartedException();

        if (!func_num_args())
            throw new WrongArgumentException('missing argument(s)');

        foreach (func_get_args() as $arg) {
            if (!isset($_SESSION[$arg]))
                return false;
        }

        return true;
    }

    /**
     * @throws SessionWrapperNotStartedException
     **/
    public function get($var)
    {
        if (!self::isStarted())
            throw new SessionWrapperNotStartedException();

        return isset($_SESSION[$var]) ? $_SESSION[$var] : null;
    }

    /**
     * @throws WrongArgumentException
     * @throws SessionWrapperNotStartedException
     **/
    /* void */
    public function &getAll()
    {
        return $_SESSION;
    }

    /**
     * @throws SessionWrapperNotStartedException
     **/
    /* void */

    public function dropAll()
    {
        if (!self::isStarted())
            throw new SessionWrapperNotStartedException();

        if ($_SESSION) {
            foreach (array_keys($_SESSION) as $key) {
                self::drop($key);
            }
        }
    }


    public function drop(/* ... */)
    {
        if (!self::isStarted())
            throw new SessionWrapperNotStartedException();

        if (!func_num_args())
            throw new WrongArgumentException('missing argument(s)');

        foreach (func_get_args() as $arg)
            unset($_SESSION[$arg]);
    }

    /**
     * assigns to $_SESSION scope variables defined in given array
     **/
    /* void */

    public function arrayAssign($scope, $array)
    {
        Assert::isArray($array);

        foreach ($array as $var) {
            if (isset($scope[$var])) {
                $_SESSION[$var] = $scope[$var];
            }
        }
    }

    /**
     * @throws SessionWrapperNotStartedException
     **/
    public function getName()
    {
        if (!self::isStarted())
            throw new SessionWrapperNotStartedException();

        return session_name();
    }

    /**
     * @throws SessionWrapperNotStartedException
     **/
    public function getId()
    {
        if (!self::isStarted())
            throw new SessionWrapperNotStartedException();

        return session_id();
    }

    /* void */
    public function commit()
    {
        session_commit();
    }
}