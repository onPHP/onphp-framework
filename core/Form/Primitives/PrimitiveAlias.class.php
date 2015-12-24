<?php
/****************************************************************************
 *   Copyright (C) 2008-2010 by Konstantin V. Arkhipov                      *
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
final class PrimitiveAlias extends BasePrimitive
{
    /** @var BasePrimitive  */
    private $primitive = null;

    /**
     * PrimitiveAlias constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

    }

    /**
     * @return null
     */
    public function getInner()
    {
        return $this->primitive;
    }

    /**
     * @param BasePrimitive $primitive
     * @return $this
     */
    public function setPrimitive(BasePrimitive $primitive)
    {
        $this->primitive = $primitive;
        return $this;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->primitive->getDefault();
    }

    /**
     * @return PrimitiveAlias
     **/
    public function setDefault($default)
    {
        $this->primitive->setDefault($default);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->primitive->getValue();
    }

    /**
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->primitive->getRawValue();
    }

    /**
     * @return mixed
     */
    public function getValueOrDefault()
    {
        return $this->primitive->getValueOrDefault();
    }

    /**
     * @return null
     */
    public function getActualValue()
    {
        return $this->primitive->getValueOrDefault();
    }

    /**
     * @return null
     */
    public function getSafeValue()
    {
        return $this->primitive->getSafeValue();
    }

    /**
     * @return null
     */
    public function getFormValue()
    {
        if (!$this->primitive->isImported()) {
            if ($this->primitive->getValue() === null) {
                return null;
            }

            return $this->primitive->exportValue();
        }

        return $this->primitive->getRawValue();
    }

    /**
     * @return PrimitiveAlias
     **/
    public function setValue($value)
    {
        $this->primitive->setValue($value);

        return $this;
    }

    /**
     * @return PrimitiveAlias
     **/
    public function dropValue()
    {
        $this->primitive->dropValue();

        return $this;
    }

    /**
     * @return PrimitiveAlias
     **/
    public function setRawValue($raw)
    {
        $this->primitive->setRawValue($raw);

        return $this;
    }

    /**
     * @return bool
     */
    public function isImported()
    {
        return $this->primitive->isImported();
    }

    /**
     * @return PrimitiveAlias
     **/
    public function clean()
    {
        $this->primitive->clean();

        return $this;
    }

    /**
     * @param $value
     * @return bool|null
     */
    public function importValue($value)
    {
        return $this->primitive->importValue($value);
    }

    /**
     * @return null
     */
    public function exportValue()
    {
        return $this->primitive->exportValue();
    }

    /**
     * @return null
     */
    public function getCustomError()
    {
        return $this->primitive->getCustomError();
    }

    /**
     * @param $scope
     * @return bool|null
     */
    public function import($scope)
    {
        if (array_key_exists($this->name, $scope)) {
            return
                $this->primitive->import(
                    [$this->primitive->getName() => $scope[$this->name]]
                );
        }

        return null;
    }
}
