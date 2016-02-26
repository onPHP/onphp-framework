<?php
/****************************************************************************
 *   Copyright (C) 2004-2009 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * Complete Form class.
 *
 * @ingroup Form
 * @ingroup Module
 *
 * @see http://onphp.org/examples.Form.en.html
 **/
class Form extends RegulatedForm
{
    const
        WRONG = 0x0001,
        MISSING = 0x0002;

    /** @var array */
    private $errors = [];
    /** @var array */
    private $labels = [];
    /** @var array */
    private $describedLabels = [];

    /** @var EntityProto  */
    private $proto = null;

    /** @var bool */
    private $importFiltering = true;

    /**
     * @param $name
     * @return bool
     */
    public function hasError($name) : bool
    {
        return array_key_exists($name, $this->errors)
        || array_key_exists($name, $this->violated);
    }

    /**
     * @param $name
     * @return null
     */
    public function getError($name)
    {
        if (array_key_exists($name, $this->errors)) {
            return $this->errors[$name];
        } elseif (array_key_exists($name, $this->violated)) {
            return $this->violated[$name];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getInnerErrors() : array
    {
        $result = $this->getErrors();

        foreach ($this->primitives as $name => $prm) {
            if (
                (
                    ($prm instanceof PrimitiveFormsList)
                    || ($prm instanceof PrimitiveForm)
                )
                && $prm->getValue()
            ) {
                if ($errors = $prm->getInnerErrors()) {
                    $result[$name] = $errors;
                } else {
                    unset($result[$name]);
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getErrors() : array
    {
        return array_merge($this->errors, $this->violated);
    }

    /**
     * @return Form
     */
    public function dropAllErrors() : Form
    {
        $this->errors = [];
        $this->violated = [];

        return $this;
    }

    /**
     * @return Form
     */
    public function enableImportFiltering() : Form
    {
        $this->importFiltering = true;

        return $this;
    }

    /**
     * @return Form
     **/
    public function disableImportFiltering() : Form
    {
        $this->importFiltering = false;

        return $this;
    }

    /**
     * primitive marking
     **/

    /**
     * @param $primitiveName
     * @param null $label
     * @return Form
     */
    public function markMissing($primitiveName, $label = null)
    {
        return $this->markCustom($primitiveName, Form::MISSING, $label);
    }

    /**
     * Set's custom error mark for primitive.
     *
     * @param $primitiveName
     * @param $customMark
     * @param null $label
     * @return Form
     * @throws MissingElementException
     * @throws WrongArgumentException
     */
    public function markCustom($primitiveName, $customMark, $label = null) : Form
    {
        Assert::isInteger($customMark);

        $this->errors[$this->get($primitiveName)->getName()] = $customMark;

        if ($label !== null) {
            $this->addCustomLabel($primitiveName, $customMark, $label);
        }

        return $this;
    }

    /**
     * @return Form
     **/
    public function addCustomLabel($primitiveName, $customMark, $label)
    {
        return $this->addErrorLabel($primitiveName, $customMark, $label);
    }

    /**
     * Assigns specific label for given primitive and error type.
     * One more example of horrible documentation style.
     *
     * @param $name
     * @param $errorType
     * @param $label
     * @return Form
     * @throws MissingElementException
     */
    private function addErrorLabel($name, $errorType, $label) : Form
    {
        if (
            !isset($this->rules[$name])
            && !$this->get($name)->getName()
        ) {
            throw new MissingElementException(
                "knows nothing about '{$name}'"
            );
        }

        $this->labels[$name][$errorType] = $label;

        return $this;
    }
    //@}

    /**
     * @param $name
     * @param null $label
     * @return $this
     * @throws MissingElementException
     */
    public function markWrong($name, $label = null)
    {
        if (isset($this->primitives[$name])) {
            $this->errors[$name] = self::WRONG;
        } elseif (isset($this->rules[$name])) {
            $this->violated[$name] = self::WRONG;
        } else {
            throw new MissingElementException(
                $name . ' does not match known primitives or rules'
            );
        }

        if ($label !== null) {
            $this->addWrongLabel($name, $label);
        }

        return $this;
    }

    /**
     * @param $primitiveName
     * @param $label
     * @return Form
     * @throws MissingElementException
     */
    public function addWrongLabel($primitiveName, $label)
    {
        return $this->addErrorLabel($primitiveName, Form::WRONG, $label);
    }

    /**
     * Returns plain list of error's labels
     *
     * @return array
     */
    public function getTextualErrors() : array
    {
        $list = [];

        foreach (array_keys($this->labels) as $name) {
            if ($label = $this->getTextualErrorFor($name)) {
                $list[] = $label;
            }
        }

        return $list;
    }

    /**
     * @param $name
     * @return null
     */
    public function getTextualErrorFor($name)
    {
        if (
        isset(
            $this->violated[$name],
            $this->labels[$name][$this->violated[$name]]
        )
        ) {
            return $this->labels[$name][$this->violated[$name]];
        } elseif (
        isset(
            $this->errors[$name],
            $this->labels[$name][$this->errors[$name]]
        )
        ) {
            return $this->labels[$name][$this->errors[$name]];
        } else {
            return null;
        }
    }

    /**
     * @param $name
     * @return null
     */
    public function getErrorDescriptionFor($name)
    {
        if (
        isset(
            $this->violated[$name],
            $this->describedLabels[$name][$this->violated[$name]]
        )
        ) {
            return $this->describedLabels[$name][$this->violated[$name]];
        } elseif (
        isset(
            $this->errors[$name],
            $this->describedLabels[$name][$this->errors[$name]]
        )
        ) {
            return $this->describedLabels[$name][$this->errors[$name]];
        } else {
            return null;
        }
    }

    /**
     * @param $name
     * @param $errorType
     * @param $description
     * @return Form
     * @throws MissingElementException
     */
    public function addErrorDescription($name, $errorType, $description) : Form
    {

        if (
            !isset($this->rules[$name])
            && !$this->get($name)->getName()
        ) {
            throw new MissingElementException(
                "knows nothing about '{$name}'"
            );
        }

        $this->describedLabels[$name][$errorType] = $description;

        return $this;
    }

    /**
     * @param $primitiveName
     * @param $label
     * @return Form
     * @throws MissingElementException
     */
    public function addMissingLabel($primitiveName, $label)
    {
        return $this->addErrorLabel($primitiveName, Form::MISSING, $label);
    }

    /**
     * @param $primitiveName
     * @return null
     */
    public function getWrongLabel($primitiveName)
    {
        return $this->getErrorLabel($primitiveName, Form::WRONG);
    }

    /**
     * @param $name
     * @param $errorType
     * @return null
     * @throws MissingElementException
     */
    private function getErrorLabel($name, $errorType)
    {
        // checks for primitive's existence
        $this->get($name);

        if (isset($this->labels[$name][$errorType])) {
            return $this->labels[$name][$errorType];
        }

        return null;
    }

    /**
     * @param $primitiveName
     * @return null
     */
    public function getMissingLabel($primitiveName)
    {
        return $this->getErrorLabel($primitiveName, Form::MISSING);
    }

    /**
     * @param $scope
     * @return Form
     */
    public function import($scope) : Form
    {
        foreach ($this->primitives as $prm) {
            $this->importPrimitive($scope, $prm);
        }

        return $this;
    }

    /**
     * @param $scope
     * @param BasePrimitive $prm
     * @return Form
     */
    private function importPrimitive($scope, BasePrimitive $prm)
    {

        if (!$this->importFiltering) {
            if ($prm instanceof FiltrablePrimitive) {

                $chain = $prm->getImportFilter();

                $prm->dropImportFilters();

                $result = $this->checkImportResult(
                    $prm,
                    $prm->import($scope)
                );

                $prm->setImportFilter($chain);

                return $result;

            } elseif ($prm instanceof PrimitiveForm) {
                return $this->checkImportResult(
                    $prm,
                    $prm->unfilteredImport($scope)
                );
            }
        }

        return $this->checkImportResult($prm, $prm->import($scope));
    }

    /**
     * @param BasePrimitive $prm
     * @param $result
     * @return Form
     * @throws MissingElementException
     */
    private function checkImportResult(BasePrimitive $prm, $result) : Form
    {
        if (
            $prm instanceof PrimitiveAlias
            && $result !== null
        ) {
            $this->markGood($prm->getInner()->getName());
        }

        $name = $prm->getName();

        if (null === $result) {
            if ($prm->isRequired()) {
                $this->errors[$name] = self::MISSING;
            }

        } elseif (true === $result) {
            unset($this->errors[$name]);

        } elseif ($error = $prm->getCustomError()) {

            $this->errors[$name] = $error;

        } else {
            $this->errors[$name] = self::WRONG;
        }

        return $this;
    }

    /**
     * @param $primitiveName
     * @return Form
     * @throws MissingElementException
     */
    public function markGood($primitiveName) : Form
    {
        if (isset($this->primitives[$primitiveName])) {
            unset($this->errors[$primitiveName]);
        } elseif (isset($this->rules[$primitiveName])) {
            unset($this->violated[$primitiveName]);
        } else {
            throw new MissingElementException(
                $primitiveName . ' does not match known primitives or rules'
            );
        }

        return $this;
    }

    /**
     * @param $scope
     * @return Form
     */
    public function importMore($scope) : Form
    {
        foreach ($this->primitives as $prm) {
            /**@var BasePrimitive $prm */
            if (!$prm->isImported()) {
                $this->importPrimitive($scope, $prm);
            }
        }

        return $this;
    }

    /**
     * @param $primitiveName
     * @param $scope
     * @return Form
     * @throws MissingElementException
     */
    public function importOne($primitiveName, $scope) : Form
    {
        return $this->importPrimitive($scope, $this->get($primitiveName));
    }

    /**
     * @param $primitiveName
     * @param $value
     * @return Form
     * @throws MissingElementException
     */
    public function importValue($primitiveName, $value) : Form
    {
        $prm = $this->get($primitiveName);

        return $this->checkImportResult($prm, $prm->importValue($value));
    }

    /**
     * @param $primitiveName
     * @param $scope
     * @return $this|Form
     * @throws MissingElementException
     */
    public function importOneMore($primitiveName, $scope) : Form
    {
        $prm = $this->get($primitiveName);

        if (!$prm->isImported()) {
            return $this->importPrimitive($scope, $prm);
        }

        return $this;
    }

    /**
     * @param $primitiveName
     * @return null
     * @throws MissingElementException
     */
    public function exportValue($primitiveName)
    {
        return $this->get($primitiveName)->exportValue();
    }

    /**
     * @return array
     */
    public function export() : array
    {
        $result = [];

        foreach ($this->primitives as $name => $prm) {
            /**@var BasePrimitive $prm */
            if ($prm->isImported()) {
                $result[$name] = $prm->exportValue();
            }
        }

        return $result;
    }

    /**
     * @param $value
     * @return null
     */
    public function toFormValue($value)
    {
        if ($value instanceof FormField) {
            return $this->getValue($value->getName());
        } elseif ($value instanceof LogicalObject) {
            return $value->toBoolean($this);
        } else {
            return $value;
        }
    }

    /**
     * @return EntityProto
     **/
    public function getProto()
    {
        return $this->proto;
    }

    /**
     * @return Form
     **/
    public function setProto(EntityProto $proto) : Form
    {
        $this->proto = $proto;

        return $this;
    }

    /**
     * @see __clone
     */
    public function __clone()
    {
        foreach ($this->primitives as $name => $primitive) {
            $this->primitives[$name] = clone $primitive;
        }
    }
}
