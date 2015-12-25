<?php
/****************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                              *
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
class PrimitiveNoValue extends BasePrimitive
{
    /**
     * @param $value
     * @return PrimitiveNoValue
     * @throws WrongArgumentException
     */
    public function setValue($value) : PrimitiveNoValue
    {
        Assert::isUnreachable('No value!');

        return $this;
    }

    /**
     * @param $default
     * @return PrimitiveNoValue
     * @throws WrongArgumentException
     */
    public function setDefaultValue($default) : PrimitiveNoValue
    {
        Assert::isUnreachable('No default value!');

        return $this;
    }

    /**
     * @param $raw
     * @return PrimitiveNoValue
     * @throws WrongArgumentException
     */
    public function setRawValue($raw) : PrimitiveNoValue
    {
        Assert::isUnreachable('No raw value!');

        return $this;
    }

    /**
     * @param $value
     * @return PrimitiveNoValue
     * @throws WrongArgumentException
     */
    public function importValue($value) : PrimitiveNoValue
    {
        Assert::isUnreachable('No import value!');

        return $this;
    }

    /**
     * @param $scope
     * @return bool|null
     */
    public function import($scope)
    {
        if (
            array_key_exists($this->name, $scope)
            && $scope[$this->name] == null
        ) {
            return $this->imported = true;
        }

        return null;
    }
}
