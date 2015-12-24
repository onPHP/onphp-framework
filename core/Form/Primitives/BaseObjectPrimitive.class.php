<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * @ingroup Primitives
 **/
abstract class BaseObjectPrimitive extends BasePrimitive
{
    /** @var null  */
    protected $className = null;

    /**
     * @param $scope
     * @return bool|null
     * @throws WrongArgumentException
     */
    public function import($scope)
    {
        if (!BasePrimitive::import($scope)) {
            return null;
        }

        if ($scope[$this->getName()] instanceof $this->className) {
            $this->value = $scope[$this->getName()];

            return true;
        }

        try {
            $this->value = new $this->className($scope[$this->getName()]);

            return true;
        } catch (WrongArgumentException $e) {
            return false;
        }

        Assert::isUnreachable();
    }

    /**
     * @param $value
     * @return BaseObjectPrimitive
     * @throws WrongArgumentException
     */
    public function setValue($value)
    {
        Assert::isInstance($value, $this->className);

        $this->value = $value;

        return $this;
    }

    /**
     * @param $default
     * @return BaseObjectPrimitive
     * @throws WrongArgumentException
     */
    public function setDefault($default)
    {
        Assert::isInstance($default, $this->className);

        $this->default = $default;

        return $this;
    }
}

?>