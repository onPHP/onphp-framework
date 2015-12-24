<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Parent of every Primitive.
 *
 * @ingroup Primitives
 * @ingroup Module
 **/
abstract class BasePrimitive
{
    /** @var null  */
    protected $name = null;

    /** @var null  */
    protected $default = null;

    /** @var Date  */
    protected $value = null;

    /** @var bool  */
    protected $required = false;

    /** @var bool  */
    protected $imported = false;

    /** @var null  */
    protected $raw = null;

    /** @var null  */
    protected $customError = null;

    /**
     * BasePrimitive constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param $default
     * @return BasePrimitive
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return BasePrimitive
     **/
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return null
     */
    public function getRawValue()
    {
        return $this->raw;
    }

    /**
     * @return null
     */
    public function getValueOrDefault()
    {
        if ($this->value !== null) {
            return $this->value;
        }

        return $this->default;
    }

    /**
     * @deprecated since version 1.0
     * @see getSafeValue, getValueOrDefault
     */
    public function getActualValue()
    {
        if ($this->value !== null) {
            return $this->value;
        } elseif ($this->imported) {
            return $this->raw;
        }

        return $this->default;
    }

    /**
     * @return null
     */
    public function getSafeValue()
    {
        if ($this->imported) {
            return $this->value;
        }

        return $this->default;
    }

    /**
     * @return BasePrimitive
     **/
    public function dropValue()
    {
        $this->value = null;

        return $this;
    }

    /**
     * usually, you should not use this method
     *
     * @param $raw
     * @return BasePrimitive
     */
    public function setRawValue($raw)
    {
        $this->raw = $raw;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired() : bool
    {
        return $this->required;
    }

    /**
     * @return BasePrimitive
     **/
    public function setRequired($really = false)
    {
        $this->required = (true === $really ? true : false);

        return $this;
    }

    /**
     * @return BasePrimitive
     **/
    public function required()
    {
        $this->required = true;

        return $this;
    }

    /**
     * @return BasePrimitive
     **/
    public function optional()
    {
        $this->required = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isImported()
    {
        return $this->imported;
    }

    /**
     * @param $value
     * @return bool|null
     */
    public function importValue($value)
    {
        return $this->import([$this->getName() => $value]);
    }

    /**
     * @param $scope
     * @return bool|null
     */
    protected function import($scope)
    {
        if (
            !empty($scope[$this->name])
            || (
                isset($scope[$this->name])
                && $scope[$this->name] !== ''
            )
        ) {
            $this->raw = $scope[$this->name];

            return $this->imported = true;
        }

        $this->clean();

        return null;
    }

    /**
     * @return $this
     */
    public function clean()
    {
        $this->raw = null;
        $this->value = null;
        $this->imported = false;

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
     * @return BasePrimitive
     **/
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null
     */
    public function exportValue()
    {
        return $this->value;
    }

    /**
     * @return null
     */
    public function getCustomError()
    {
        return $this->customError;
    }
}
