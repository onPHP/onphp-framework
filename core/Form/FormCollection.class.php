<?php

/****************************************************************************
 *   Copyright (C) 2013 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
final class FormCollection implements Iterator
{
    /** @var Form|null */
    private $sampleForm = null;

    /** @var array */
    private $primitiveNames = [];

    /** @var bool */
    private $imported = false;

    /** @var array */
    private $formList = [];

    /**
     * FormCollection constructor.
     * @param Form $sample
     */
    public function __construct(Form $sample)
    {
        $this->sampleForm = $sample;
    }

    /**
     * @deprecated
     * @param Form $sample
     * @return FormCollection
     */
    public static function create(Form $sample)
    {
        return new self($sample);
    }

    /**
     * from http request
     * looks like foo[1]=42&bar[1]=test&foo[2]=44&bar[2]=anothertest
     *
     * @param array $scope
     * @return FormCollection
     * @throws WrongArgumentException
     */
    public function import(array $scope) : FormCollection
    {
        $this->imported = true;

        foreach ($scope as $name => $paramList) {

            /**
             * @var array $paramList
             * looks like array(1 => 42, 2 => 44)
             */
            Assert::isArray($paramList);

            foreach ($paramList as $key => $value) {
                if (!isset($this->formList[$key])) {
                    $this->formList[$key] = clone $this->sampleForm;
                }
                $this->formList[$key]->importMore([$name => $value]);
            }
        }

        reset($this->formList);

        return $this;
    }

    /**
     * @return mixed
     * @throws WrongArgumentException
     */
    public function current()
    {
        Assert::isTrue($this->imported, "Import scope in me before try to iterate");

        return current($this->formList);
    }

    /**
     * @return mixed
     * @throws WrongArgumentException
     */
    public function key()
    {
        Assert::isTrue($this->imported, "Import scope in me before try to iterate");

        return key($this->formList);
    }

    /**
     * @return mixed
     * @throws WrongArgumentException
     */
    public function next()
    {
        Assert::isTrue($this->imported, "Import scope in me before try to iterate");

        return next($this->formList);
    }

    /**
     * @return mixed
     * @throws WrongArgumentException
     */
    public function rewind()
    {
        Assert::isTrue($this->imported, "Import scope in me before try to iterate");

        return reset($this->formList);
    }

    /**
     * @return bool
     * @throws WrongArgumentException
     */
    public function valid()
    {
        Assert::isTrue($this->imported, "Import scope in me before try to iterate");

        return (key($this->formList) !== null);
    }
}