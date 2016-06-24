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
class PrimitiveJson extends PrimitiveArray {
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
    public function setFetchMode($ternary) {
        Assert::isTernaryBase($ternary);
        $this->fetchMode = $ternary;
        return $this;
    }

    /**
     * @param $scope
     * @return bool|null
     * @throws WrongArgumentException
     */
    public function import($scope) {
        if (!BasePrimitive::import($scope))
            return null;
        if (!is_array($scope[$this->name])) {
            try {
                $this->value = json_decode($scope[$this->name], 1); //to assoc array
            } catch (Exception $e) {
                //Only UTF-8, for instance!
                throw new WrongArgumentException('String in json field should be valid JSON');
            }
        } else {
            $this->value = $scope[$this->name];
        }
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

    /**
     * @param $value
     * @return bool|null
     * @throws WrongArgumentException
     */
    public function importValue($value) {
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
            } elseif (!$value->isFetched())
                return null;
        }
        return $this->import(array($this->getName() => $value));
    }

    /**
     * @return string
     */
    public function exportValue()
    {
        return json_encode(parent::exportValue());
    }
}