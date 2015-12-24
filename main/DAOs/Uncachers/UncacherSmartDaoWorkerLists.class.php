<?php
/***************************************************************************
 *   Copyright (C) 2012 by Aleksey S. Denisov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Uncachers
 **/
class UncacherSmartDaoWorkerLists implements UncacherBase
{
    private $classNameMap = array();

    public function __construct($className, $indexKey, $intKey)
    {
        $this->classNameMap[$className] = array($indexKey, $intKey);
    }

    /**
     * @deprecated
     * @return UncacherSmartDaoWorkerLists
     */
    public static function create($className, $indexKey, $intKey)
    {
        return new self($className, $indexKey, $intKey);
    }

    public function getClassNameMap()
    {
        return $this->classNameMap;
    }

    /**
     * @param UncacherBase $uncacher
     * @return UncacherBaseDaoWorker
     * @throws WrongArgumentException
     */
    public function merge(UncacherBase $uncacher)
    {
        Assert::isInstance($uncacher, get_class($this));
        return $this->mergeSelf($uncacher);
    }

    /**
     * @param UncacherSmartDaoWorkerLists $uncacher
     * @return $this
     */
    private function mergeSelf(UncacherSmartDaoWorkerLists $uncacher)
    {
        foreach ($uncacher->getClassNameMap() as $className => $classNameRow) {
            if (!isset($this->classNameMap[$className])) {
                $this->classNameMap[$className] = $classNameRow;
            }
        }
        return $this;
    }

    public function uncache()
    {
        foreach ($this->classNameMap as $className => $classNameRow) {
            list ($indexKey, $intKey) = $classNameRow;
            $this->uncacheClassName($className, $indexKey, $intKey);
        }
    }

    protected function uncacheClassName($className, $indexKey, $intKey)
    {
        $cache = Cache::me();
        $pool = SemaphorePool::me();

        if ($pool->get($intKey)) {
            $indexList = $cache->mark($className)->get($indexKey);
            $cache->mark($className)->delete($indexKey);

            if ($indexList) {
                foreach (array_keys($indexList) as $key)
                    $cache->mark($className)->delete($key);
            }

            $pool->free($intKey);

            return true;
        }

        $cache->mark($className)->delete($indexKey);

        return false;
    }
}