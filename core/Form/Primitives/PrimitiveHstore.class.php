<?php
/****************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                                *
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
class PrimitiveHstore extends BasePrimitive
{
    protected $formMapping = [];


    /**
     * @return array
     */
    public function getInnerErrors() : array
    {
        if ($this->value instanceof Form) {
            return $this->value->getInnerErrors();
        }

        return [];
    }

    /**
     * @return Form
     */
    public function getInnerForm()
    {
        return $this->value;
    }

    /**
     * @return Hstore|null
     */
    public function getValue()
    {
        if (!$this->value instanceof Form) {
            return null;
        }

        return Hstore::make($this->value->export());
    }

    /**
     * @param $value
     * @return bool|null
     * @throws WrongArgumentException
     */
    public function importValue($value) : bool
    {
        if ($value === null) {
            return parent::importValue(null);
        }

        Assert::isTrue($value instanceof Hstore, 'importValue');

        if (!$this->value instanceof Form) {
            $this->value = $this->makeForm();
        }

        $this->value->import($value->getList());
        $this->imported = true;

        return
            $this->value->getErrors()
                ? false
                : true;
    }

    /**
     * @return Form
     */
    protected function makeForm()
    {
        $form = new Form();

        foreach ($this->getFormMapping() as $primitive) {
            $form->add($primitive);
        }

        return $form;
    }

    /**
     * @return array
     */
    public function getFormMapping() : array
    {
        return $this->formMapping;
    }

    /**
     * @return PrimitiveHstore
     **/
    public function setFormMapping($array)
    {
        $this->formMapping = $array;

        return $this;
    }

    /**
     * @param $scope
     * @return bool|null
     */
    public function import($scope)
    {
        if (!isset($scope[$this->name])) {
            return null;
        }

        $this->rawValue = $scope[$this->name];

        if (!$this->value instanceof Form) {
            $this->value = $this->makeForm();
        }

        $this->value->import($this->rawValue);

        $this->imported = true;

        if ($this->value->getErrors()) {
            return false;
        }

        return true;
    }

    /**
     * @return Hstore
     **/
    public function exportValue()
    {
        if (!$this->value instanceof Form) {
            return null;
        }

        return !$this->value->getErrors()
            ? $this->value->export()
            : null;
    }
}
