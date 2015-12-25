<?php
/****************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
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
class PrimitiveArray extends FiltrablePrimitive
{
    /**
     * Fetching strategy for incoming containers:
     *
     * null - do nothing;
     * true - lazy fetch;
     * false - full fetch.
     **/
    private $fetchMode = null;

    /**
     * @return PrimitiveArray
     **/
    public function setFetchMode($ternary)
    {
        Assert::isTernaryBase($ternary);

        $this->fetchMode = $ternary;

        return $this;
    }

    /**
     * @param $value
     * @return bool|null
     */
    public function importValue($value)
    {
        if ($value instanceof UnifiedContainer) {
            if (
                ($this->fetchMode !== null)
                && ($value->getParentObject()->getId())
            ) {
                if ($value->isLazy() === $this->fetchMode) {
                    $value = $value->getList();
                } else {
                    $className = get_class($value);

                    $containter = new $className(
                        $value->getParentObject(),
                        $this->fetchMode
                    );

                    $value = $containter->getList();
                }
            } elseif (!$value->isFetched()) {
                return null;
            }
        }

        if (is_array($value)) {
            return $this->import([$this->getName() => $value]);
        }

        return false;
    }

    /**
     * @param $scope
     * @return bool|null
     */
    public function import($scope)
    {
        if (!BasePrimitive::import($scope)) {
            return null;
        }

        $this->value = $scope[$this->name];

        $this->selfFilter();

        if (
            is_array($this->value)
            && !($this->min && count($this->value) < $this->min)
            && !($this->max && count($this->value) > $this->max)
        ) {
            return true;
        } else {
            $this->value = null;
        }

        return false;
    }
}