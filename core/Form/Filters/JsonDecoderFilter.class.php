<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
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
class JsonDecoderFilter extends BaseFilter
{
    private $assoc = false;

    /**
     * @return $this
     **/
    public static function me()
    {
        return Singleton::getInstance(__CLASS__);
    }

    /**
     * @param bool|true $orly
     * @return $this
     */
    public function setAssoc($orly = true)
    {
        $this->assoc = (true === $orly);

        return $this;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function apply($value)
    {
        return json_decode($value, $this->assoc);
    }
}
