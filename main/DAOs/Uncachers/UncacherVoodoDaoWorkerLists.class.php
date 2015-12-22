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
class UncacherVoodoDaoWorkerLists implements UncacherBase
{
    private $handlerList = array();

    public function __construct($className, SegmentHandler $handler)
    {
        $this->handlerList[$className] = $handler;
    }

    /**
     * @return UncacherBaseDaoWorker
     */
    public static function create($className, SegmentHandler $handler)
    {
        return new self($className, $handler);
    }

    /**
     * @param $uncacher UncacherVoodoDaoWorkerLists same as self class
     * @return UncacherBase (this)
     */
    public function merge(UncacherBase $uncacher)
    {
        Assert::isInstance($uncacher, get_class($this));
        return $this->mergeSelf($uncacher);
    }

    /**
     * @param UncacherVoodoDaoWorkerLists $uncacher
     * @return UncacherVoodoDaoWorkerLists
     */
    private function mergeSelf(UncacherVoodoDaoWorkerLists $uncacher)
    {
        foreach ($uncacher->getHandlerList() as $className => $handler) {
            if (!isset($this->handlerList[$className])) {
                $this->handlerList[$className] = $handler;
            }
        }
        return $this;
    }

    public function getHandlerList()
    {
        return $this->handlerList;
    }

    public function uncache()
    {
        foreach ($this->handlerList as $className => $handler) {
            $this->uncacheClassName($className, $handler);
        }
    }

    protected function uncacheClassName($className, SegmentHandler $handler)
    {
        $handler->drop();

        $dao = ClassUtils::callStaticMethod($className . '::dao');
        /* @var $dao StorableDAO */
        return Cache::worker($dao)->uncacheByQuery($dao->makeSelectHead());
    }
}

?>