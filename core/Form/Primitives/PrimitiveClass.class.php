<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
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
class PrimitiveClass extends PrimitiveString
{
    /** @var null  */
    private $ofClassName = null;

    /**
     * @param $scope
     * @return bool|null
     * @throws WrongArgumentException
     */
    public function import($scope)
    {
        if (!($result = parent::import($scope))) {
            return $result;
        }

        if (
            !ClassUtils::isClassName($scope[$this->name])
            || !$this->classExists($scope[$this->name])
            || (
                $this->ofClassName
                && !ClassUtils::isInstanceOf(
                    $scope[$this->name],
                    $this->ofClassName
                )
            )
        ) {
            $this->value = null;

            return false;
        }

        return true;
    }

    /**
     * @param $name
     * @return bool
     */
    private function classExists($name)
    {
        try {
            return class_exists($name, true);
        } catch (ClassNotFoundException $e) {
            return false;
        }
    }

    /**
     * @throws WrongArgumentException
     * @return PrimitiveIdentifier
     **/
    public function of($class)
    {
        $className = $this->guessClassName($class);

        Assert::isTrue(
            class_exists($className, true)
            || interface_exists($className, true),
            "knows nothing about '{$className}' class/interface"
        );

        $this->ofClassName = $className;

        return $this;
    }

    /**
     * @param $class
     * @return string
     * @throws WrongArgumentException
     */
    private function guessClassName($class)
    {
        if (is_string($class)) {
            return $class;
        } elseif (is_object($class)) {
            return get_class($class);
        }

        throw new WrongArgumentException('strange class given - ' . $class);
    }
}
