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
 * @ingroup Filters
 **/
final class TrimFilter implements Filtrator
{
    const LEFT = 'l';
    const RIGHT = 'r';
    const BOTH = null;

    private $charlist = null;
    private $direction = self::BOTH;

    /**
     * @deprecated
     * @return TrimFilter
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return TrimFilter
     **/
    public function setLeft() : TrimFilter
    {
        $this->direction = self::LEFT;

        return $this;
    }

    /**
     * @return TrimFilter
     */
    public function setRight() : TrimFilter
    {
        $this->direction = self::RIGHT;

        return $this;
    }

    /**
     * @return TrimFilter
     */
    public function setBoth() : TrimFilter
    {
        $this->direction = self::BOTH;

        return $this;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function apply($value)
    {
        $function = $this->direction . 'trim';

        return (
        $this->charlist
            ? $function($value, $this->charlist)
            : $function($value)
        );
    }

    /**
     * @param $charlist
     * @return TrimFilter
     */
    public function setCharlist($charlist) : TrimFilter
    {
        $this->charlist = $charlist;

        return $this;
    }
}