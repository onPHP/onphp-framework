<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Garmonbozia Research Group                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @see Identifiable
 *
 * @ingroup Base
 * @ingroup Module
 **/
class Identifier implements Identifiable
{
    private $id = null;
    private $final = false;


    /**
     * @param $id
     * @return Identifier
     */
    public static function wrap($id) : Identifier
    {
        return (new Identifier())->setId($id);
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Identifier
     **/
    public function setId($id) : Identifier
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Identifier
     **/
    public function finalize() : Identifier
    {
        $this->final = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFinalized()
    {
        return $this->final;
    }
}
