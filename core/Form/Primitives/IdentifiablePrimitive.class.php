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
abstract class IdentifiablePrimitive extends PrimitiveInteger // parent class doesn't really matter here
{
    /** @var null  */
    protected $className = null;

    /**
     * due to historical reasons, by default we're dealing only with
     * integer identifiers, this problem correctly fixed in master branch
     */

    /** @var bool  */
    protected $scalar = false;

    /** @var string  */
    private $extractMethod = 'getId';

    /**
     * @param $class
     * @return string
     * @throws WrongArgumentException
     */
    protected static function guessClassName($class)
    {
        if (is_string($class)) {
            return $class;
        } elseif (is_object($class)) {
            if ($class instanceof Identifiable) {
                return get_class($class);
            } elseif ($class instanceof GenericDAO) {
                return $class->getObjectName();
            }
        }

        throw new WrongArgumentException('strange class given - ' . $class);
    }

    /**
     * @param $className
     * @return mixed
     */
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

    /**
     * @return bool
     */
    public function isScalar() : bool
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

    /**
     * @return mixed|null
     */
    public function exportValue()
    {
        if (!$this->value) {
            return null;
        }

        return $this->actualExportValue($this->value);
    }

    /**
     * @param $value
     * @return mixed|null
     * @throws WrongArgumentException
     */
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

    /**
     * @param $number
     * @throws WrongArgumentException
     */
    protected function checkNumber($number)
    {
        if ($this->scalar) {
            Assert::isScalar($number);
        } else {
            Assert::isInteger($number);
        }
    }

    /**
     * @param $number
     * @return int
     */
    protected function castNumber($number) : int
    {
        if (!$this->scalar && Assert::checkInteger($number)) {
            return (int) $number;
        }

        return $number;
    }
}
