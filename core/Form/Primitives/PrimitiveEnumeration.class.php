<?php
/*****************************************************************************
 *   Copyright (C) 2006-2008 by Ivan Y. Khvostishkov, Konstantin V. Arkhipov *
 *                                                                           *
 *   This program is free software; you can redistribute it and/or modify    *
 *   it under the terms of the GNU Lesser General Public License as          *
 *   published by the Free Software Foundation; either version 3 of the      *
 *   License, or (at your option) any later version.                         *
 *                                                                           *
 *****************************************************************************/

/**
 * @ingroup Primitives
 **/
class PrimitiveEnumeration extends IdentifiablePrimitive
{
    /**
     * @return mixed
     * @throws WrongArgumentException
     */
    public function getList()
    {
        if ($this->value) {
            return $this->value->getObjectList();
        } elseif ($this->default) {
            return $this->default->getObjectList();
        } else {
            $object = new $this->className(
                call_user_func([$this->className, 'getAnyId'])
            );

            return $object->getObjectList();
        }

        Assert::isUnreachable();
    }

    /**
     * @param $class
     * @return PrimitiveEnumeration
     * @throws WrongArgumentException
     */
    public function of($class)
    {
        $className = $this->guessClassName($class);

        Assert::classExists($className);

        Assert::isInstance($className, 'Enumeration');

        $this->className = $className;

        return $this;
    }

    /**
     * @param $value
     * @return bool|null
     * @throws WrongArgumentException
     * @throws WrongStateException
     */
    public function importValue(/* Identifiable */
        $value
    ) {
        if ($value) {
            Assert::isEqual(get_class($value), $this->className);
        } else {
            return parent::importValue(null);
        }

        return $this->import([$this->getName() => $value->getId()]);
    }

    /**
     * @param $scope
     * @return bool|null
     * @throws WrongStateException
     */
    public function import($scope)
    {
        if (!$this->className) {
            throw new WrongStateException(
                "no class defined for PrimitiveEnumeration '{$this->name}'"
            );
        }

        $result = parent::import($scope);

        if ($result === true) {
            try {
                $this->value = new $this->className($this->value);
            } catch (MissingElementException $e) {
                $this->value = null;

                return false;
            }

            return true;
        }

        return $result;
    }
}
