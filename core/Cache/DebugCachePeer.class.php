<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * CachePeer for debugging and logging puproses.
 *
 * @ingroup Cache
 **/
class DebugCachePeer extends SelectivePeer
{
    private $peer = null;
    private $logger = null;
    private $isWeb = true;
    private $whiteListActions = [];
    private $blackListActions = [];
    private $actionFilter = false;

    /**
     * DebugCachePeer constructor.
     * @param CachePeer $peer
     * @param $logfile
     * @param bool $isWeb
     * @param bool $appendFile
     */
    public function __construct(CachePeer $peer, $logfile, $isWeb = true, $appendFile = true)
    {
        $this->peer = $peer;
        $this->isWeb = $isWeb;
        $this->logger =
            StreamLogger::create()->
            setOutputStream(FileOutputStream::create($logfile, $appendFile));
    }

    /**
     * @param CachePeer $peer
     * @param $logfile
     * @param bool $isWeb
     * @param bool $appendFile
     * @return DebugCachePeer
     */
    public static function create(CachePeer $peer, $logfile, $isWeb = true, $appendFile = true) : DebugCachePeer
    {
        return new self($peer, $logfile, $isWeb, $appendFile);
    }

    /**
     * @param $actions
     * @return DebugCachePeer
     * @throws WrongStateException
     */
    public function setBlackListActions($actions) : DebugCachePeer
    {
        if (!empty($this->whiteListActions)) {
            throw new WrongStateException('You already setup black list!');
        }

        $this->blackListActions = $actions;

        $this->actionFilter = true;

        return $this;
    }

    /**
     * @return DebugCachePeer
     */
    public function dropBlackListActions() : DebugCachePeer
    {
        $this->blackListActions = [];

        $this->actionFilter = false;

        return $this;
    }

    /**
     * @param $actions
     * @return DebugCachePeer
     * @throws WrongStateException
     */
    public function setWhiteListActions($actions) : DebugCachePeer
    {
        if (!empty($this->blackListActions)) {
            throw new WrongStateException('You already setup white list!');
        }

        $this->whiteListActions = $actions;

        $this->actionFilter = true;

        return $this;
    }

    /**
     * @return DebugCachePeer
     */
    public function dropWhiteListActions() : DebugCachePeer
    {
        $this->whiteListActions = [];

        $this->actionFilter = false;

        return $this;
    }

    /**
     * @return CachePeer
     * @param $className
     * @return $this
     */
    public function mark($className)
    {
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function increment($key, $value)
    {
        $beginTime = microtime(true);
        $value = $this->peer->increment($key, $value);
        $totalTime = microtime(true) - $beginTime;

        $this->log('increment', $totalTime, $key);

        return $value;
    }

    /**
     * @param $action
     * @param $totalTime
     * @param null $key
     * @return DebugCachePeer
     */
    private function log($action, $totalTime, $key = null) : DebugCachePeer
    {
        if ($this->actionFilter) {
            if (
                !empty($this->blackListActions)
                && in_array($action, $this->blackListActions)
            ) {
                return $this;
            }

            if (
                !empty($this->whiteListActions)
                && !in_array($action, $this->whiteListActions)
            ) {
                return $this;
            }
        }

        $record = null;

        if ($this->isWeb) {
            $record .= (
                (isset($_SERVER['SSI_REQUEST_URI']))
                    ? $_SERVER['SSI_REQUEST_URI']
                    : $_SERVER['REQUEST_URI']
                ) . "\t";
        }

        $record .= $action . "\t" . $key . "\t" . $totalTime;

        $this->logger->info($record);

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function decrement($key, $value)
    {
        $beginTime = microtime(true);
        $value = $this->peer->decrement($key, $value);
        $totalTime = microtime(true) - $beginTime;

        $this->log('decrement', $totalTime, $key);

        return $value;
    }

    /**
     * @param $indexes
     * @return mixed
     */
    public function getList($indexes)
    {
        $beginTime = microtime(true);
        $value = $this->peer->getList($indexes);
        $totalTime = microtime(true) - $beginTime;

        $this->log('getList', $totalTime, implode(',', $indexes));

        return $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $beginTime = microtime(true);
        $value = $this->peer->get($key);
        $totalTime = microtime(true) - $beginTime;

        $this->log('get', $totalTime, $key);

        return $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        $beginTime = microtime(true);
        $value = $this->peer->delete($key);
        $totalTime = microtime(true) - $beginTime;

        $this->log('delete', $totalTime, $key);

        return $value;
    }

    /**
     * @return CachePeer
     **/
    public function clean()
    {
        $beginTime = microtime(true);
        $value = $this->peer->clean();
        $totalTime = microtime(true) - $beginTime;

        $this->log('clean', $totalTime);

        return $value;
    }

    /**
     * @return bool
     */
    public function isAlive()
    {
        $beginTime = microtime(true);
        $value = $this->peer->isAlive();
        $totalTime = microtime(true) - $beginTime;

        $this->log('isAlive', $totalTime);

        return $value;
    }

    /**
     * @param $key
     * @param $data
     * @return mixed
     */
    public function append($key, $data)
    {
        $beginTime = microtime(true);
        $value = $this->peer->append($key, $data);
        $totalTime = microtime(true) - $beginTime;

        $this->log('append', $totalTime, $key);

        return $value;
    }

    /**
     * @param $action
     * @param $key
     * @param $value
     * @param int $expires
     * @return mixed
     */
    protected function store(
        $action,
        $key,
        $value,
        $expires = Cache::EXPIRES_MEDIUM
    ) {
        $beginTime = microtime(true);
        $value = $this->peer->store($action, $key, $value, $expires);
        $totalTime = microtime(true) - $beginTime;

        $this->log('store + ' . $action, $totalTime, $key);

        return $value;
    }
}