<?php

/***************************************************************************
 *   Copyright (C) 2011 by Alexey Denisov                                  *
 *   alexeydsov@gmail.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class MockServiceLocator extends ServiceLocator
{
    protected $store = [];
    protected $objectList = [];

    /**
     * @return MockServiceLocator
     */
    public static function create()
    {
        return new self;
    }

    /**
     * @param string $className
     * @param object $object
     * @return MockServiceLocator
     */
    public function addSpawnObject($className, $object)
    {
        $this->objectList[$className][] = $object;
        return $this;
    }

    /**
     * @param array $objectList
     * @return MockServiceLocator
     */
    public function setObjectList(array $objectList)
    {
        $this->objectList = $objectList;
        return $this;
    }

    /**
     * @param string $className
     * @return object
     */
    public function spawn($className)
    {
        if (isset($this->objectList[$className])) {
            $classNameList = $this->objectList[$className];
            if (empty($classNameList)) {
                throw new WrongStateException("Object list for class '{$className}' already empty");
            }
            $object = reset($classNameList);
            unset($classNameList[key($classNameList)]);
            return $this->implementSelf($object);
        } else {
            throw new WrongStateException("Class '{$className}' was not added for spawn");
        }
    }

    /**
     * @param object $object
     * @return object
     */
    protected function implementSelf($object)
    {
        $object = parent::implementSelf($object);
        if ($object instanceof IServiceLocatorSupport) {
            $subLocator = $object->getServiceLocator();
            $subLocator->setObjectList();
        }
        return $object;
    }
}

?>