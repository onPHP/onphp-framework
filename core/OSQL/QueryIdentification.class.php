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
 * @ingroup OSQL
 * @ingroup Module
 **/
abstract class QueryIdentification implements Query
{
    /**
     * @return string
     */
    public function getId()
    {
        return sha1($this->toString());
    }

    /**
     * @return mixed
     */
    public function toString()
    {
        return $this->toDialectString(ImaginaryDialect::me());
    }

    /**
     * @param $id
     * @throws UnsupportedMethodException
     */
    final public function setId($id)
    {
        throw new UnsupportedMethodException();
    }
}
