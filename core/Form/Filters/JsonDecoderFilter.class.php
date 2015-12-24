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
final class JsonDecoderFilter extends BaseFilter
{
    private $assoc = false;

    /**
     * @return JsonDecoderFilter
     **/
    public static function me() : JsonDecoderFilter
    {
        return Singleton::getInstance(__CLASS__);
    }

    /**
     * @return JsonDecoderFilter
     **/
    public function setAssoc($orly = true) : JsonDecoderFilter
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