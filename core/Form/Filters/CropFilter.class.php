<?php
/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @see RegulatedPrimitive::addImportFilter()
 *
 * @ingroup Filters
 **/
class CropFilter implements Filtrator
{
    private $start = 0;
    private $length = 0;

    /**
     * @deprecated
     * @return CropFilter
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @param $start
     * @return CropFilter
     * @throws WrongArgumentException
     */
    public function setStart($start) : CropFilter
    {
        Assert::isPositiveInteger($start);

        $this->start = $start;

        return $this;
    }

    /**
     * @param $length
     * @return CropFilter
     * @throws WrongArgumentException
     */
    public function setLength($length) : CropFilter
    {
        Assert::isPositiveInteger($length);

        $this->length = $length;

        return $this;
    }

    /**
     * @param $value
     * @return string
     */
    public function apply($value) : string
    {
        return
            $this->length
                ? mb_strcut($value, $this->start, $this->length)
                : mb_strcut($value, $this->start);
    }
}
