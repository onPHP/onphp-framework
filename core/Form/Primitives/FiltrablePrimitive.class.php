<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * Basis for Primitives which can be filtered.
 *
 * @ingroup Primitives
 * @ingroup Module
 **/
abstract class FiltrablePrimitive extends RangedPrimitive
{
    /** @var FilterChain|null  */
    private $importFilter = null;

    /** @var FilterChain|null  */
    private $displayFilter = null;

    /**
     * FiltrablePrimitive constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->displayFilter = new FilterChain();
        $this->importFilter = new FilterChain();
    }

    /**
     * @param Filtrator $filter
     * @return FiltrablePrimitive
     */
    public function addDisplayFilter(Filtrator $filter) : FiltrablePrimitive
    {
        $this->displayFilter->add($filter);

        return $this;
    }

    /**
     * @return FiltrablePrimitive
     */
    public function dropDisplayFilters() : FiltrablePrimitive
    {
        $this->displayFilter = new FilterChain();

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getDisplayValue()
    {
        if (is_array($value = $this->getSafeValue())) {
            foreach ($value as &$element) {
                $element = $this->displayFilter->apply($element);
            }

            return $value;
        }

        return $this->displayFilter->apply($value);
    }

    /**
     * @return FiltrablePrimitive
     **/
    public function addImportFilter(Filtrator $filter) : FiltrablePrimitive
    {
        $this->importFilter->add($filter);

        return $this;
    }

    /**
     * @return FiltrablePrimitive
     **/
    public function dropImportFilters() : FiltrablePrimitive
    {
        $this->importFilter = new FilterChain();

        return $this;
    }

    /**
     * @return FilterChain
     **/
    public function getImportFilter()
    {
        return $this->importFilter;
    }

    /**
     * @param FilterChain $chain
     * @return FiltrablePrimitive
     */
    public function setImportFilter(FilterChain $chain) : FiltrablePrimitive
    {
        $this->importFilter = $chain;

        return $this;
    }

    /**
     * @return FilterChain
     **/
    public function getDisplayFilter()
    {
        return $this->displayFilter;
    }

    /**
     * @param FilterChain $chain
     * @return FiltrablePrimitive
     */
    public function setDisplayFilter(FilterChain $chain) : FiltrablePrimitive
    {
        $this->displayFilter = $chain;

        return $this;
    }

    /**
     * @return FiltrablePrimitive
     **/
    protected function selfFilter()
    {
        if (is_array($this->value)) {
            foreach ($this->value as &$value) {
                $value = $this->importFilter->apply($value);
            }
        } else {
            $this->value = $this->importFilter->apply($this->value);
        }

        return $this;
    }
}
