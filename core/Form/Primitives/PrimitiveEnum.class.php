<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Primitives
 **/
class PrimitiveEnum extends IdentifiablePrimitive implements ListedPrimitive
{
    /**
     * @return mixed
     * @throws WrongArgumentException
     */
    public function getList()
    {
        if ($this->value) {
            return ClassUtils::callStaticMethod(get_class($this->value) . '::getList');
        } elseif ($this->default) {
            return ClassUtils::callStaticMethod(get_class($this->default) . '::getList');
        } else {
            $object = new $this->className(
                ClassUtils::callStaticMethod($this->className . '::getAnyId')
            );

            return $object->getObjectList();
        }

        Assert::isUnreachable();
    }

    /**
     * @throws WrongArgumentException
     * @return PrimitiveEnum
     **/
    public function of($class)
    {
        $className = $this->guessClassName($class);

        Assert::classExists($className);

        Assert::isInstance($className, 'Enum');

        $this->className = $className;

        return $this;
    }

    /**
     * @param Identifiable $value
     * @return bool|null
     * @throws WrongArgumentException
     */
    public function importValue($value)
    {
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
        $result = parent::import($scope);

        if ($result === true) {
            try {
                $this->value = $this->makeEnumById($this->value);
            } catch (MissingElementException $e) {
                $this->value = null;

                return false;
            }

            return true;
        }

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     * @throws WrongStateException
     */
    protected function makeEnumById($id)
    {
        if (!$this->className) {
            throw new WrongStateException(
                "no class defined for PrimitiveEnum '{$this->name}'"
            );
        }

        return new $this->className($id);
    }

    /**
     * @param $list
     * @throws UnsupportedMethodException
     */
    public function setList($list)
    {
        throw new UnsupportedMethodException('you cannot set list here, it is impossible, because list getted from enum classes');
    }

    /**
     * @return null
     */
    public function getActualChoiceValue()
    {
        if (
            !$this->getChoiceValue() &&
            $this->getDefault()
        ) {
            return $this->getDefault()->getName();
        }

        return null;
    }

    /**
     * @return null
     */
    public function getChoiceValue()
    {
        if (
            ($value = $this->getValue()) &&
            $value instanceof Enum
        ) {
            return $value->getName();
        }

        return null;
    }
}
