<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
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
abstract class BaseLogger
{
    private $level = null;

    /**
     * @return LogLevel
     **/
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return Logger
     **/
    public function setLevel(LogLevel $level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Logger
     **/
    final public function severe($message)
    {
        $this->log(LogLevel::severe(), $message);

        return $this;
    }

    /**
     * @return Logger
     **/
    final public function log(LogLevel $level, $message)
    {
        $this->logRecord(
            (new LogRecord())
                ->setLevel($level)
                ->setMessage($message)
        );

        return $this;
    }

    /**
     * @return Logger
     **/
    final public function logRecord(LogRecord $record)
    {
        $levelMatches =
            $this->level === null
            || $record->getLevel()->getId() <= $this->level->getId();

        if ($levelMatches && $this->isLoggable($record)) {
            $this->publish($record);
        }

        return $this;
    }

    /**
     * you may override me
     **/
    protected function isLoggable(LogRecord $record)
    {
        return true;
    }

    /**
     * @return Logger
     **/
    abstract protected function publish(LogRecord $record);

    /**
     * @return Logger
     **/
    final public function warning($message)
    {
        $this->log(LogLevel::warning(), $message);

        return $this;
    }

    /**
     * @return Logger
     **/
    final public function info($message)
    {
        $this->log(LogLevel::info(), $message);

        return $this;
    }

    /**
     * @return Logger
     **/
    final public function config($message)
    {
        $this->log(LogLevel::config(), $message);

        return $this;
    }

    /**
     * @return Logger
     **/
    final public function fine($message)
    {
        $this->log(LogLevel::fine(), $message);

        return $this;
    }

    /**
     * @return Logger
     **/
    final public function finer($message)
    {
        $this->log(LogLevel::finer(), $message);

        return $this;
    }

    /**
     * @return Logger
     **/
    final public function finest($message)
    {
        $this->log(LogLevel::finest(), $message);

        return $this;
    }
}

?>