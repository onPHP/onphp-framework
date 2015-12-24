<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Ivan Y. Khvostishkov                       *
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
class PrimitiveForm extends BasePrimitive
{
    /**
     * @var null
     */
    protected $proto = null;

    /** @var  Form */
    protected $value;

    /**
     * @var bool
     */
    private $composite = false;

    /**
     * @throws WrongArgumentException
     * @return PrimitiveForm
     **/
    public function ofProto(EntityProto $proto)
    {
        $this->proto = $proto;

        return $this;
    }

    /**
     * @param AbstractProtoClass $proto
     * @return $this
     */
    public function ofAutoProto(AbstractProtoClass $proto)
    {
        $this->proto = $proto;

        return $this;
    }

    /**
     * @return bool
     */
    public function isComposite() : bool
    {
        return $this->composite;
    }

    /**
     * @return PrimitiveForm
     *
     * Either composition or aggregation, it is very important on import.
     **/
    public function setComposite($composite = true)
    {
        $this->composite = ($composite == true);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->proto->className();
    }

    /**
     * @return null
     */
    public function getProto()
    {
        return $this->proto;
    }

    /**
     * @throws WrongArgumentException
     * @return PrimitiveForm
     **/
    public function setValue($value)
    {
        Assert::isTrue($value instanceof Form);

        return parent::setValue($value);
    }

    /**
     * @param $value
     * @return bool
     * @throws WrongArgumentException
     * @throws WrongStateException
     */
    public function importValue($value)
    {
        if ($value !== null) {
            Assert::isTrue($value instanceof Form);
        }

        if (!$this->value || !$this->composite) {
            $this->value = $value;
        } else {
            throw new WrongStateException(
                'composite objects should not be broken'
            );
        }

        return ($value->getErrors() ? false : true);
    }

    /**
     * @return null
     */
    public function exportValue()
    {
        if (!$this->value) {
            return null;
        }

        return $this->value->export();
    }

    /**
     * @return array
     */
    public function getInnerErrors()
    {
        if ($this->value) {
            return $this->value->getInnerErrors();
        }

        return [];
    }

    /**
     * @param $scope
     * @return bool|null
     * @throws WrongStateException
     */
    public function import($scope)
    {
        return $this->actualImport($scope, true);
    }

    /**
     * @param $scope
     * @param $importFiltering
     * @return bool|null
     * @throws WrongArgumentException
     * @throws WrongStateException
     */
    private function actualImport($scope, $importFiltering)
    {
        if (!$this->proto) {
            throw new WrongStateException(
                "no proto defined for PrimitiveForm '{$this->name}'"
            );
        }

        if (!isset($scope[$this->name])) {
            return null;
        }

        $this->rawValue = $scope[$this->name];

        if (!$this->value || !$this->composite) {
            $this->value = $this->proto->makeForm();
        }

        if (!$importFiltering) {
            $this->value
                ->disableImportFiltering()
                ->import($this->rawValue)
                ->enableImportFiltering();
        } else {
            $this->value->import($this->rawValue);
        }

        $this->imported = true;

        if ($this->value->getErrors()) {
            return false;
        }

        return true;
    }

    /**
     * @param $scope
     * @return bool|null
     * @throws WrongStateException
     */
    public function unfilteredImport($scope)
    {
        return $this->actualImport($scope, false);
    }
}
