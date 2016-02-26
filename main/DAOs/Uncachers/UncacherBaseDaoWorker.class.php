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
class UncacherBaseDaoWorker implements UncacherBase
{
    private $classNameMap = [];

    public function __construct($className, $idKey)
    {
        $this->classNameMap[$className] = [$idKey];
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
     * @param UncacherBaseDaoWorker $uncacher
     * @return UncacherBaseDaoWorker
     */
    private function mergeSelf(UncacherBaseDaoWorker $uncacher)
    {
        foreach ($uncacher->getClassNameMap() as $className => $idKeys) {
            if (isset($this->classNameMap[$className])) {
                $this->classNameMap[$className] = ArrayUtils::mergeUnique(
                    $this->classNameMap[$className],
                    $idKeys
                );
            } else {
                $this->classNameMap[$className] = $idKeys;
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getClassNameMap()
    {
        return $this->classNameMap;
    }

    /**
     *
     */
    public function uncache()
    {
        foreach ($this->classNameMap as $className => $idKeys) {
            foreach ($idKeys as $key) {
                $this->uncacheClassName($className, $idKeys);
            }
        }
    }

    /**
     * @param $className
     * @param $idKeys
     */
    protected function uncacheClassName($className, $idKeys)
    {
        foreach ($idKeys as $key)
            Cache::me()->mark($className)->delete($key);
    }
}

