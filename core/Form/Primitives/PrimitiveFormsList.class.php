<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Ivan Y. Khvostishkov                       *
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
class PrimitiveFormsList extends PrimitiveForm
{
    /** @var array  */
    protected $value = [];

    /**
     * @return PrimitiveFormsList
     **/
    public function clean()
    {
        parent::clean();

        $this->value = array();

        return $this;
    }

    public function setComposite($composite = true)
    {
        throw new UnsupportedMethodException(
            'composition is not supported for lists'
        );
    }

    public function getInnerErrors()
    {
        $result = [];

        foreach ($this->getValue() as $id => $form) {
            if ($errors = $form->getInnerErrors())
                $result[$id] = $errors;
        }

        return $result;
    }

    public function import($scope)
    {
        if (!$this->proto)
            throw new WrongStateException(
                "no proto defined for PrimitiveFormsList '{$this->name}'"
            );

        if (!BasePrimitive::import($scope))
            return null;

        if (!is_array($scope[$this->name]))
            return false;

        $error = false;

        $this->value = [];

        foreach ($scope[$this->name] as $id => $value) {
            $this->value[$id] = $this->proto
                ->makeForm()
                ->import($value);

            if ($this->value[$id]->getErrors())
                $error = true;
        }

        return !$error;
    }

    public function importValue($value)
    {
        if ($value !== null)
            Assert::isArray($value);
        else
            return null;

        $result = true;

        $resultValue = [];

        foreach ($value as $id => $form) {
            Assert::isInstance($form, 'Form');

            $resultValue[$id] = $form;

            if ($form->getErrors())
                $result = false;
        }

        $this->value = $resultValue;

        return $result;
    }

    public function exportValue()
    {
        if (!$this->isImported())
            return null;

        $result = [];

        foreach ($this->value as $id => $form) {
            $result[$id] = $form->export();
        }

        return $result;
    }
}