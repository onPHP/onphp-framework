<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
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
class StripTagsFilter implements Filtrator
{
    private $exclude = null;

    /**
     * @return StripTagsFilter
     **/
    public function setAllowableTags($exclude)
    {
        if (null !== $exclude) {
            Assert::isString($exclude);
        }

        $this->exclude = $exclude;

        return $this;
    }

    /**
     * @param $value
     * @return string
     */
    public function apply($value)
    {
        return strip_tags($value, $this->exclude);
    }
}