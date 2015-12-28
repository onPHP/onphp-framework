<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Html
 * @ingroup Module
 **/
final class Cdata extends SgmlToken
{
    private $data = null;

    private $strict = false;

    /**
     * @deprecated
     * @return Cdata
     **/
    public static function create()
    {
        return new self;
    }

    public function getData()
    {
        if ($this->strict) {
            return '<![CDATA[' . $this->data . ']]>';
        } else {
            return $this->data;
        }
    }

    /**
     * @return Cdata
     **/
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getRawData()
    {
        return $this->data;
    }

    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * @return Cdata
     **/
    public function setStrict($isStrict)
    {
        Assert::isBoolean($isStrict);

        $this->strict = $isStrict;

        return $this;
    }
}

?>