<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
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
 class IdentifiablePrimitive
    extends PrimitiveInteger // parent class doesn't really matter here
{
    protected $className = null;
    private $extractMethod = 'getId';

    /**
     * due to historical reasons, by default we're dealing only with
     * integer identifiers, this problem correctly fixed in master branch
     */
    protected $scalar = false;

    abstract public function of($className);

    /**
     * @param mixed $extractMethod
     * @return IdentifiablePrimitive
     */
    public function setExtractMethod($extractMethod)
    {
        if (is_callable($extractMethod)) {
            /* all ok, call what you want */
        } elseif (strpos($extractMethod, '::') === false) {
            Assert::isTrue(
                method_exists($this->className, $extractMethod),
                "knows nothing about '" . $this->className
                . "::{$extractMethod}' method"
            );
        } else {
            ClassUtils::checkStaticMethod($extractMethod);
        }

        $this->extractMethod = $extractMethod;

        return $this;
    }

    /**
     * @return string
     **/
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return IdentifiablePrimitive
     **/
    public function setScalar($orly = false)
    {
        $this->scalar = ($orly === true);

        return $this;
    }

    public function isScalar()
    {
        return $this->scalar;
    }

    /**
     * @throws WrongArgumentException
     * @return IdentifiablePrimitive
     **/
    public function setValue($value)
    {
        $className = $this->className;

        Assert::isNotNull($this->className);

        Assert::isTrue($value instanceof $className);

        return parent::setValue($value);
    }

    protected static function guessClassName($class)
    {
        if (is_string($class))
            return $class;
        elseif (is_object($class)) {
            if ($class instanceof Identifiable)
                return get_class($class);
            elseif ($class instanceof GenericDAO)
                return $class->getObjectName();
        }

        throw new WrongArgumentException('strange class given - ' . $class);
    }

    public function exportValue()
    {
        if (!$this->value)
            return null;

        return $this->actualExportValue($this->value);
    }

    /* void */
    protected function checkNumber($number)
    {
        if ($this->scalar)
            Assert::isScalar($number);
        else
            Assert::isInteger($number);
    }

    protected function castNumber($number)
    {
        if (!$this->scalar && Assert::checkInteger($number))
            return (int)$number;

        return $number;
    }

    protected function actualExportValue($value)
    {
        if (!ClassUtils::isInstanceOf($value, $this->className)) {
            return null;
        }

        if (is_callable($this->extractMethod)) {
            return call_user_func($this->extractMethod, $value);
        } elseif (strpos($this->extractMethod, '::') === false) {
            return $value->{$this->extractMethod}($value);
        } else {
            ClassUtils::callStaticMethod($this->extractMethod, $value);
        }
    }
}

?>