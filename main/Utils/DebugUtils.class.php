<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Utils
 **/
final class DebugUtils extends StaticFactory
{
    private static $memoryAccumulator = 0;
    private static $currentMemory = null;

    public static function el($vr, $prefix = null)
    {
        if ($prefix === null) {
            $trace = debug_backtrace();
            $prefix = basename($trace[0]['file']) . ':' . $trace[0]['line'];
        }

        error_log($prefix . ': ' . var_export($vr, true));
    }

    public static function ev($vr, $prefix = null)
    {
        if ($prefix === null) {
            $trace = debug_backtrace();
            $prefix = basename($trace[0]['file']) . ':' . $trace[0]['line'];
        }

        echo
            '<pre>'
            . $prefix . ': ' . htmlspecialchars(var_export($vr, true))
            . '</pre>';
    }

    public static function ec($vr, $prefix = null)
    {
        if ($prefix === null) {
            $trace = debug_backtrace();
            $prefix = basename($trace[0]['file']) . ':' . $trace[0]['line'];
        }

        echo "\n" . $prefix . ': ' . var_export($vr, true) . "\n";
    }

    public static function eq(Query $query, $prefix = null)
    {
        if ($prefix === null) {
            $trace = debug_backtrace();
            $prefix = basename($trace[0]['file']) . ':' . $trace[0]['line'];
        }

        error_log(
            $prefix . ": " . $query->toDialectString(
                DBPool::me()->getLink()->getDialect()
            )
        );
    }

    public static function microtime()
    {
        list($usec, $sec) = explode(' ', microtime(), 2);
        return ((float) $usec + (float) $sec);
    }

    public static function setMemoryCounter()
    {
        self::$currentMemory = memory_get_usage();
    }

    public static function addMemoryCounter()
    {
        self::$memoryAccumulator += memory_get_usage() - self::$currentMemory;
    }

    public static function getMemoryCounter()
    {
        return self::$memoryAccumulator;
    }

    public static function errorMav($message = null)
    {
        $uri =
            (
            isset($_SERVER['HTTP_HOST'])
                ? $_SERVER['HTTP_HOST']
                : null
            )
            . (
            isset($_SERVER['REQUEST_URI'])
                ? $_SERVER['REQUEST_URI']
                : null
            );

        return
            (new ModelAndView())
                ->setView('error')
                ->setModel(
                    (new Model())
                        ->set(
                            'errorMessage',
                            ($message ? $message . ': ' : null) . $uri
                        )
                );
    }
}

?>