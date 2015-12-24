<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * SHA-1 based filter: passwords.
 *
 * @ingroup Filters
 **/
final class HashFilter implements Filtrator
{
    /** @var bool  */
    private $binary = false;

    /**
     * HashFilter constructor.
     * @param bool $binary
     */
    public function __construct($binary = false)
    {
        $this->binary = ($binary === true);
    }

    /**
     * @deprecated
     * @return HashFilter
     **/
    public static function create($binary = false)
    {
        return new self($binary);
    }

    /**
     * @return bool
     */
    public function isBinary()
    {
        return $this->binary;
    }

    /**
     * @param $value
     * @return string
     */
    public function apply($value) : string
    {
        return sha1($value, $this->binary);
    }
}
