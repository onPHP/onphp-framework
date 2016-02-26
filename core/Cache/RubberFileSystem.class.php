<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Simple filesystem cache.
 *
 * @ingroup Cache
 **/
class RubberFileSystem extends CachePeer
{
    private $directory = null;

    /**
     * RubberFileSystem constructor.
     * @param string $directory
     * @throws WrongArgumentException
     */
    public function __construct($directory = 'cache/')
    {
        $directory = ONPHP_TEMP_PATH . $directory;

        if (!is_writable($directory)) {
            if (!mkdir($directory, 0700, true)) {
                throw new WrongArgumentException(
                    "can not write to '{$directory}'"
                );
            }
        }

        if ($directory[strlen($directory) - 1] != DIRECTORY_SEPARATOR) {
            $directory .= DIRECTORY_SEPARATOR;
        }

        $this->directory = $directory;
    }

    /**
     * @return bool
     */
    public function isAlive()
    {
        if (!is_writable($this->directory)) {
            return mkdir($this->directory, 0700, true);
        } else {
            return true;
        }
    }

    /**
     * @return RubberFileSystem
     **/
    public function clean()
    {
        // just to return 'true'
        FileUtils::removeDirectory($this->directory, true);

        return parent::clean();
    }

    /**
     * @param $key
     * @param $value
     * @return mixed|null
     */
    public function increment($key, $value)
    {
        $path = $this->makePath($key);

        if (null !== ($current = $this->operate($path))) {
            $this->operate($path, $current += $value);

            return $current;
        }

        return null;
    }

    /**
     * @param $key
     * @return string
     */
    private function makePath($key) : string
    {
        return
            $this->directory
            . $key[0] . $key[1]
            . DIRECTORY_SEPARATOR
            . substr($key, 2);
    }

    /**
     * @param $path
     * @param null $value
     * @param null $expires
     * @return mixed|null
     * @throws WrongArgumentException
     */
    private function operate($path, $value = null, $expires = null)
    {
        $key = hexdec(substr(md5($path), 3, 2)) + 1;

        $pool = SemaphorePool::me();

        if (!$pool->get($key)) {
            return null;
        }

        try {
            $old = umask(0077);
            $fp = fopen($path, $value !== null ? 'wb' : 'rb');
            umask($old);
        } catch (BaseException $e) {
            $pool->drop($key);
            return null;
        }

        if ($value !== null) {
            fwrite($fp, $this->prepareData($value));
            fclose($fp);

            if ($expires < parent::TIME_SWITCH) {
                $expires += time();
            }

            try {
                touch($path, $expires);
            } catch (BaseException $e) {
                // race-removed
            }

            return $pool->drop($key);
        } else {
            if (($size = filesize($path)) > 0) {
                $data = fread($fp, $size);
            } else {
                $data = null;
            }

            fclose($fp);

            $pool->drop($key);

            return $data ? $this->restoreData($data) : null;
        }

        Assert::isUnreachable();
    }

    /**
     * @param $key
     * @param $value
     * @return mixed|null
     */
    public function decrement($key, $value)
    {
        $path = $this->makePath($key);

        if (null !== ($current = $this->operate($path))) {
            $this->operate($path, $current -= $value);

            return $current;
        }

        return null;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        $path = $this->makePath($key);

        if (is_readable($path)) {

            if (filemtime($path) <= time()) {
                try {
                    unlink($path);
                } catch (BaseException $e) {
                    // we're in race with unexpected clean()
                }
                return null;
            }

            return $this->operate($path);
        }

        return null;
    }

    /**
     * @param $key
     * @param $data
     * @return bool
     */
    public function append($key, $data) : bool
    {
        $path = $this->makePath($key);

        $directory = dirname($path);

        if (!file_exists($directory)) {
            try {
                mkdir($directory);
            } catch (BaseException $e) {
                // we're in race
            }
        }

        if (!is_writable($path)) {
            return false;
        }

        try {
            $fp = fopen($path, 'ab');
        } catch (BaseException $e) {
            return false;
        }

        fwrite($fp, $data);

        fclose($fp);

        return true;
    }

    /**
     * @param $action
     * @param $key
     * @param $value
     * @param int $expires
     * @return bool
     */
    protected function store($action, $key, $value, $expires = 0) : bool
    {
        $path = $this->makePath($key);
        $time = time();

        $directory = dirname($path);

        if (!file_exists($directory)) {
            try {
                mkdir($directory);
            } catch (BaseException $e) {
                // we're in race
            }
        }

        // do not add, if file exist and not expired
        if (
            $action == 'add'
            && is_readable($path)
            && filemtime($path) > $time
        ) {
            return true;
        }

        // do not replace, when file not exist or expired
        if ($action == 'replace') {

            if (!is_readable($path)) {
                return false;
            } elseif (filemtime($path) <= $time) {
                $this->delete($key);
                return false;
            }
        }

        $this->operate($path, $value, $expires);

        return true;
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key) : bool
    {
        try {
            unlink($this->makePath($key));
        } catch (BaseException $e) {
            return false;
        }

        return true;
    }
}
