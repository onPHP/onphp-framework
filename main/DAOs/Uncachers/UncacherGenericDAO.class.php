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
class UncacherGenericDAO implements UncacherBase
{
    private $daoMap = array();

    public function __construct(GenericDAO $dao, $id, UncacherBase $workerUncacher)
    {
        $this->daoMap[get_class($dao)] = array(array($id), $workerUncacher);
    }

    /**
     * @deprecated
     * @param GenericDAO $dao
     * @param $id
     * @param UncacherBase $workerUncacher
     * @return UncacherGenericDAO
     */
    public static function create(GenericDAO $dao, $id, UncacherBase $workerUncacher)
    {
        return new self($dao, $id, $workerUncacher);
    }

    /**
     * @param $uncacher UncacherGenericDAO same as self class
     * @return UncacherBase (this)
     */
    public function merge(UncacherBase $uncacher)
    {
        Assert::isInstance($uncacher, 'UncacherGenericDAO');
        return $this->mergeSelf($uncacher);
    }

    private function mergeSelf(UncacherGenericDAO $uncacher)
    {
        foreach ($uncacher->getDaoMap() as $daoClass => $daoMap) {
            if (isset($this->daoMap[$daoClass])) {
                //merge identities
                $this->daoMap[$daoClass][0] = ArrayUtils::mergeUnique(
                    $this->daoMap[$daoClass][0],
                    $daoMap[0]
                );
                //merge workers uncachers
                $this->daoMap[$daoClass][1]->merge($daoMap[1]);
            } else {
                $this->daoMap[$daoClass] = $daoMap;
            }
        }

        return $this;
    }

    public function getDaoMap()
    {
        return $this->daoMap;
    }

    public function uncache()
    {
        foreach ($this->daoMap as $daoClass => $uncacheData) {
            $dao = GenericDAO::getInstance($daoClass);
            /* @var $dao GenericDAO */
            list($dropIdentityIds, $workerUncacher) = $uncacheData;
            /* @var $workerUncacher UncacherBase */

            foreach ($dropIdentityIds as $id)
                $dao->dropObjectIdentityMapById($id);

            $dao->registerWorkerUncacher($workerUncacher);
        }
    }
}