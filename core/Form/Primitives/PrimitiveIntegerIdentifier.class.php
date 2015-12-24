<?php
/***************************************************************************
 *   Copyright (C) 2009 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Primitives
 **/
final class PrimitiveIntegerIdentifier extends PrimitiveIdentifier
{
    protected $scalar = false;

    /**
     * @param bool $orly
     * @return IdentifiablePrimitive|void
     * @throws WrongStateException
     */
    public function setScalar($orly = false)
    {
        throw new WrongStateException();
    }
}
