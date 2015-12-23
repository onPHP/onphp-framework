<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * File based locker.
 *
 * @ingroup Lockers
 **/
class FileLocker extends BaseLocker
{
    /**
     * @var null|string
     */
    private $directory = null;

    /**
     * FileLocker constructor.
     * @param string $directory
     * @throws WrongArgumentException
     */
    public function __construct($directory = 'file-locking/')
    {
        $this->directory = ONPHP_TEMP_PATH . $directory;

        if (!is_writable($this->directory)) {
            if (!mkdir($this->directory, 0700, true)) {
                throw new WrongArgumentException(
                    "can not write to '{$directory}'"
                );
            }
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function get($key) : bool
    {
        $this->pool[$key] = fopen($this->directory . $key, 'w+');

        return flock($this->pool[$key], LOCK_EX);
    }

    /**
     * @param $key
     * @return bool
     */
    public function free($key) : bool
    {
        return flock($this->pool[$key], LOCK_UN);
    }

    /**
     * @param $key
     * @return bool
     */
    public function drop($key) : bool
    {
        try {
            fclose($this->pool[$key]);
            return unlink($this->directory . $key);
        } catch (BaseException $e) {
            unset($this->pool[$key]); // already race-removed
            return false;
        }
    }
}