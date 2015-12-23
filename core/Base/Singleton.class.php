<?php
/***************************************************************************
 *   Copyright (C) 2004-2009 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Inheritable Singleton's pattern implementation.
 *
 * @ingroup Base
 * @ingroup Module
 **/
abstract class Singleton
{
    private static $instances = [];

    protected function __construct()
    {/* you can't create me */
    }

    /// @example singleton.php
    final public static function getInstance(
        $class,
        $args = null /* , ... */
    )
    {
        if (!isset(self::$instances[$class])) {
            // for Singleton::getInstance('class_name', $arg1, ...) calling
            if (2 < func_num_args()) {
                $args = func_get_args();
                array_shift($args);

                // emulation of ReflectionClass->newInstanceWithoutConstructor
                $object =
                    unserialize(
                        sprintf('O:%d:"%s":0:{}', strlen($class), $class)
                    );

                call_user_func_array(
                    [$object, '__construct'],
                    $args
                );
            } else {
                $object =
                    $args
                        ? new $class($args)
                        : new $class();
            }

            Assert::isTrue(
                $object instanceof Singleton,
                "Class '{$class}' is something not a Singleton's child"
            );

            self::$instances[$class] = $object;
        }

        return self::$instances[$class];
    }

    final public static function getAllInstances()
    {
        return self::$instances;
    }

    /* void */
    final public static function dropInstance($class)
    {
        if (!isset(self::$instances[$class])) {
            throw new MissingElementException('knows nothing about ' . $class);
        }

        unset(self::$instances[$class]);
    }

    final private function __clone()
    {/* do not clone me */
    }

    final private function __sleep()
    {/* restless class */
    }
}
