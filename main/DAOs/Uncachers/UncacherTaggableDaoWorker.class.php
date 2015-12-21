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
class UncacherTaggableDaoWorker implements UncacherBase
{
    private $classNameMap = array();

    public function __construct($className, $idKey, $tags, UncacherBase $worker)
    {
        $this->classNameMap[$className] = array(array($idKey), $tags, $worker);
    }

    /**
     * @param $className
     * @param $idKey
     * @param $tags
     * @param UncacherBase $worker
     * @return UncacherTaggableDaoWorker
     */
    public static function create($className, $idKey, $tags, UncacherBase $worker)
    {
        return new self($className, $idKey, $tags, $worker);
    }

    /**
     * @param UncacherBase $uncacher
     * @return UncacherTaggableDaoWorker
     * @throws WrongArgumentException
     */
    public function merge(UncacherBase $uncacher)
    {
        Assert::isInstance($uncacher, 'UncacherTaggableDaoWorker');
        return $this->mergeSelf($uncacher);
    }

    private function mergeSelf(UncacherTaggableDaoWorker $uncacher)
    {
        foreach ($uncacher->getClassNameMap() as $className => $uncaches) {
            if (!isset($this->classNameMap[$className])) {
                $this->classNameMap[$className] = $uncaches;
            } else {
                //merging idkeys
                $this->classNameMap[$className][0] = ArrayUtils::mergeUnique(
                    $this->classNameMap[$className][0],
                    $uncaches[0]
                );
                //merging tags
                $this->classNameMap[$className][1] = ArrayUtils::mergeUnique(
                    $this->classNameMap[$className][1],
                    $uncaches[1]
                );
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

    public function uncache()
    {
        foreach ($this->classNameMap as $className => $uncaches) {
            list($idKeys, $tags, $worker) = $uncaches;
            /* @var $worker UncacherBase */
            $worker->expireTags($tags);

            foreach ($idKeys as $key)
                Cache::me()->mark($className)->delete($key);

            ClassUtils::callStaticMethod("$className::dao")->uncacheLists();
        }
    }
}