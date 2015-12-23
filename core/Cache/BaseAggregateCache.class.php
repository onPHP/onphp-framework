<?php
/****************************************************************************
 *   Copyright (C) 2011 by Anton E. Lebedevich, Konstantin V. Arkhipov,     *
 *   Evgeny V. Kokovikhin                                                   *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * Base common parent for all aggregate caches
 *
 * @ingroup Cache
 **/
abstract class BaseAggregateCache extends SelectivePeer
{
    protected $peers = [];

    /**
     * @param $label
     * @return $this
     * @throws MissingElementException
     */
    public function dropPeer($label)
    {
        if (!isset($this->peers[$label])) {
            throw new MissingElementException(
                "there is no peer with '{$label}' label"
            );
        }

        unset($this->peers[$label]);

        return $this;
    }

    /**
     * low-level cache access
     *
     * @param $key
     * @param $value
     * @return null
     */
    public function increment($key, $value)
    {
        $label = $this->guessLabel($key);

        if ($this->peers[$label]['object']->isAlive()) {
            return $this->peers[$label]['object']->increment($key, $value);
        } else {
            $this->checkAlive();
        }

        return null;
    }

    /** */
    abstract protected function guessLabel($key);

    /**
     * @return BaseAggregateCache
     **/
    public function checkAlive()
    {
        $this->alive = false;

        foreach ($this->peers as $label => $peer) {
            if ($peer['object']->isAlive()) {
                $this->alive = true;
            } else {
                unset($this->peers[$label]);
            }
        }

        return $this->alive;
    }

    /**
     * @param $key
     * @param $value
     * @return null
     */
    public function decrement($key, $value)
    {
        $label = $this->guessLabel($key);

        if ($this->peers[$label]['object']->isAlive()) {
            return $this->peers[$label]['object']->decrement($key, $value);
        } else {
            $this->checkAlive();
        }

        return null;
    }

    /**
     * @param $key
     * @return null
     */
    public function get($key)
    {
        $label = $this->guessLabel($key);

        if ($this->peers[$label]['object']->isAlive()) {
            return $this->peers[$label]['object']->get($key);
        } else {
            $this->checkAlive();
        }

        return null;
    }

    /**
     * @param $indexes
     * @return array
     */
    public function getList($indexes) : array
    {
        $labels = [];
        $out = [];

        foreach ($indexes as $index) {
            $labels[$this->guessLabel($index)][] = $index;
        }

        foreach ($labels as $label => $indexList) {
            if ($this->peers[$label]['object']->isAlive()) {
                if ($list = $this->peers[$label]['object']->getList($indexList)) {
                    $out = array_merge($out, $list);
                }
            } else {
                $this->checkAlive();
            }
        }

        return $out;
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $label = $this->guessLabel($key);

        if (!$this->peers[$label]['object']->isAlive()) {
            $this->checkAlive();
            return false;
        }

        return $this->peers[$label]['object']->delete($key);
    }

    /**
     * @return AggregateCache
     **/
    public function clean()
    {
        foreach ($this->peers as $peer) {
            $peer['object']->clean();
        }

        $this->checkAlive();

        return parent::clean();
    }

    /**
     * @return array
     */
    public function getStats() : array
    {
        $stats = [];

        foreach ($this->peers as $level => $peer) {
            $stats[$level] = $peer['stat'];
        }

        return $stats;
    }

    /**
     * @param $key
     * @param $data
     * @return bool
     */
    public function append($key, $data) : bool
    {
        $label = $this->guessLabel($key);

        if ($this->peers[$label]['object']->isAlive()) {
            return $this->peers[$label]['object']->append($key, $data);
        } else {
            $this->checkAlive();
        }

        return false;
    }

    /**
     * @param $label
     * @param CachePeer $peer
     * @return BaseAggregateCache
     * @throws WrongArgumentException
     */
    protected function doAddPeer($label, CachePeer $peer) : BaseAggregateCache
    {
        if (isset($this->peers[$label])) {
            throw new WrongArgumentException(
                'use unique names for your peers'
            );
        }

        if ($peer->isAlive()) {
            $this->alive = true;
        }

        $this->peers[$label]['object'] = $peer;
        $this->peers[$label]['stat'] = [];

        return $this;
    }

    /**
     * @param $action
     * @param $key
     * @param $value
     * @param int $expires
     * @return bool
     */
    protected function store($action, $key, $value, $expires = Cache::EXPIRES_MINIMUM)
    {
        $label = $this->guessLabel($key);

        if ($this->peers[$label]['object']->isAlive()) {
            return
                $this->peers[$label]['object']->$action(
                    $key,
                    $value,
                    $expires
                );
        } else {
            $this->checkAlive();
        }

        return false;
    }
}