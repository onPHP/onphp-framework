<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Html
 **/
class SgmlOpenTag extends SgmlTag
{
    private $attributes = [];
    private $empty = false;

    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->empty;
    }

    /**
     * @return SgmlOpenTag
     **/
    public function setEmpty($isEmpty)
    {
        Assert::isBoolean($isEmpty);

        $this->empty = $isEmpty;

        return $this;
    }

    /**
     * @return SgmlOpenTag
     **/
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        $name = strtolower($name);

        return isset($this->attributes[$name]);
    }

    /**
     * @param $name
     * @return mixed
     * @throws WrongArgumentException
     */
    public function getAttribute($name)
    {
        $name = strtolower($name);

        if (!isset($this->attributes[$name])) {
            throw new WrongArgumentException(
                "attribute '{$name}' does not exist"
            );
        }

        return $this->attributes[$name];
    }

    /**
     * @param $name
     * @return $this
     * @throws WrongArgumentException
     */
    public function dropAttribute($name)
    {
        $name = strtolower($name);

        if (!isset($this->attributes[$name])) {
            throw new WrongArgumentException(
                "attribute '{$name}' does not exist"
            );
        }

        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributesList()
    {
        return $this->attributes;
    }

    /**
     * @return SgmlOpenTag
     **/
    public function dropAttributesList()
    {
        $this->attributes = [];

        return $this;
    }
}

