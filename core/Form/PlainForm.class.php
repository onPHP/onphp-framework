<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * Common Primitive-handling.
 *
 * @ingroup Form
 * @ingroup Module
 **/
abstract class PlainForm
{
    /** @var array  */
    protected $primitives = [];

    /**
     * @return $this
     */
    public function clean()
    {
        foreach ($this->primitives as $prm) {
            /**@var BasePrimitive $prm */
            $prm->clean();
        }

        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function exists($name) : bool
    {
        return isset($this->primitives[$name]);
    }

    /**
     * @param BasePrimitive $prm
     * @return $this
     * @throws WrongArgumentException
     */
    public function add(BasePrimitive $prm)
    {
        $name = $prm->getName();

        Assert::isFalse(
            isset($this->primitives[$name]),
            'i am already exists!'
        );

        $this->primitives[$name] = $prm;

        return $this;
    }

    /**
     * @param BasePrimitive $prm
     * @return $this
     */
    public function set(BasePrimitive $prm)
    {
        $this->primitives[$prm->getName()] = $prm;

        return $this;
    }

    /**
     * @param $name
     * @return $this
     * @throws MissingElementException
     */
    public function drop($name)
    {
        if (!isset($this->primitives[$name])) {
            throw new MissingElementException(
                "can not drop inexistent primitive '{$name}'"
            );
        }

        unset($this->primitives[$name]);

        return $this;
    }

    /**
     * @param $name
     * @return null
     * @throws MissingElementException
     */
    public function getValue($name)
    {
        return $this->get($name)->getValue();
    }

    /**
     * @throws MissingElementException
     * @return BasePrimitive
     **/
    public function get($name)
    {
        if (isset($this->primitives[$name])) {
            return $this->primitives[$name];
        }

        throw new MissingElementException("knows nothing about '{$name}'");
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     * @throws MissingElementException
     */
    public function setValue($name, $value)
    {
        $this->get($name)->setValue($value);

        return $this;
    }

    /**
     * @param $name
     * @return null
     * @throws MissingElementException
     */
    public function getRawValue($name)
    {
        return $this->get($name)->getRawValue();
    }

    /**
     * @param $name
     * @return null
     * @throws MissingElementException
     */
    public function getValueOrDefault($name)
    {
        return $this->get($name)->getValueOrDefault();
    }

    /**
     * @deprecated since version 1.0
     * @see getValueOrDefault
     */
    public function getActualValue($name)
    {
        return $this->get($name)->getActualValue();
    }

    /**
     * @param $name
     * @return null
     * @throws MissingElementException
     */
    public function getSafeValue($name)
    {
        return $this->get($name)->getSafeValue();
    }

    /**
     * @param $name
     * @return mixed
     * @throws MissingElementException
     * @throws WrongArgumentException
     */
    public function getChoiceValue($name)
    {
        Assert::isTrue(($prm = $this->get($name)) instanceof ListedPrimitive);

        return $prm->getChoiceValue();
    }

    /**
     * @param $name
     * @return mixed
     * @throws MissingElementException
     * @throws WrongArgumentException
     */
    public function getActualChoiceValue($name)
    {
        Assert::isTrue(($prm = $this->get($name)) instanceof ListedPrimitive);

        return $prm->getActualChoiceValue();
    }

    /**
     * @param $name
     * @return Date|mixed|null
     * @throws MissingElementException
     */
    public function getDisplayValue($name)
    {
        $primitive = $this->get($name);

        if ($primitive instanceof FiltrablePrimitive) {
            return $primitive->getDisplayValue();
        } else {
            return $primitive->getValueOrDefault();
        }
    }

    /**
     * @return array
     */
    public function getPrimitiveNames() : array
    {
        return array_keys($this->primitives);
    }

    /**
     * @return array
     */
    public function getPrimitiveList() : array
    {
        return $this->primitives;
    }
}
