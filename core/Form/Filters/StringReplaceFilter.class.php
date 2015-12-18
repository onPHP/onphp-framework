<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Filters
 **/
class StringReplaceFilter implements Filtrator
{
    private $search = null;
    private $replace = null;

    private $count = null;

    /**
     * @return StringReplaceFilter
     **/
    public static function create($search = null, $replace = null)
    {
        return new self($search, $replace);
    }

    public function __construct($search = null, $replace = null)
    {
        $this->search = $search;
        $this->replace = $replace;
    }

    /**
     * @return StringReplaceFilter
     **/
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @return StringReplaceFilter
     **/
    public function setReplace($replace)
    {
        $this->replace = $replace;

        return $this;
    }

    public function getReplace()
    {
        return $this->replace;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function apply($value)
    {
        if ($this->search === $this->replace)
            return $value;

        return
            str_replace(
                $this->search,
                $this->replace,
                $value,
                $this->count
            );
    }
}