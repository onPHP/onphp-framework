<?php
/****************************************************************************
 *   Copyright (C) 2007-2008 by Denis M. Gabaidulin, Konstantin V. Arkhipov *
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
class PrimitiveIdentifierList extends PrimitiveIdentifier
{
    /** @var array  */
    protected $value = [];
    /** @var bool  */
    private $ignoreEmpty = false;
    /** @var bool  */
    private $ignoreWrong = false;

    /**
     * @return PrimitiveIdentifierList
     */
    public function clean() : PrimitiveIdentifierList
    {
        parent::clean();

        // restoring our very own default
        $this->value = [];

        return $this;
    }

    /**
     * @param $value
     * @return PrimitiveIdentifierList
     * @throws WrongArgumentException
     */
    public function setValue($value) : PrimitiveIdentifierList
    {
        if ($value) {
            Assert::isArray($value);
            Assert::isInstance(current($value), $this->className);
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @param $value
     * @return bool|mixed|null
     * @throws WrongStateException
     */
    public function importValue($value)
    {
        if ($value instanceof UnifiedContainer) {
            if ($value->isLazy()) {
                return $this->import(
                    [$this->name => $value->getList()]
                );
            } elseif (
                $value->getParentObject()->getId()
                && ($list = $value->getList())
            ) {
                return $this->import(
                    [$this->name => ArrayUtils::getIdsArray($list)]
                );
            } else {
                return parent::importValue(null);
            }
        }

        if (is_array($value)) {
            try {
                if ($this->scalar) {
                    Assert::isScalar(current($value));
                } else {
                    Assert::isInteger(current($value));
                }

                return $this->import(
                    [$this->name => $value]
                );
            } catch (WrongArgumentException $e) {
                return $this->import(
                    [$this->name => ArrayUtils::getIdsArray($value)]
                );
            }
        }

        return parent::importValue($value);
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
                "no class defined for PrimitiveIdentifierList '{$this->name}'"
            );
        }

        if (!BasePrimitive::import($scope)) {
            return null;
        }

        if (!is_array($scope[$this->name])) {
            return false;
        }

        $list = array_unique($scope[$this->name]);

        $values = [];

        foreach ($list as $id) {
            if ((string) $id == "" && $this->isIgnoreEmpty()) {
                continue;
            }

            if (
                ($this->scalar && !Assert::checkScalar($id))
                || (!$this->scalar && !Assert::checkInteger($id))
            ) {
                if (!$this->isIgnoreWrong()) {
                    return false;
                } else {
                    continue;
                } //just skip it
            }

            $values[] = $id;
        }

        $objectList = $this->dao()->getListByIds($values);

        if (
            (
                (count($objectList) == count($values))
                || $this->isIgnoreWrong()
            )
            && !($this->min && count($values) < $this->min)
            && !($this->max && count($values) > $this->max)
        ) {
            $this->value = $objectList;
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isIgnoreEmpty() : bool
    {
        return $this->ignoreEmpty;
    }

    /**
     * @param bool $orly
     * @return PrimitiveIdentifierList
     */
    public function setIgnoreEmpty($orly = true) : PrimitiveIdentifierList
    {
        $this->ignoreEmpty = ($orly === true);

        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreWrong() : bool
    {
        return $this->ignoreWrong;
    }

    /**
     * @param bool $orly
     * @return $this
     */
    public function setIgnoreWrong($orly = true)
    {
        $this->ignoreWrong = ($orly === true);

        return $this;
    }

    /**
     * @return array|null
     */
    public function exportValue()
    {
        if (!$this->value) {
            return null;
        }

        return ArrayUtils::getIdsArray($this->value);
    }
}