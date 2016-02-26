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
class ObjectToDTOConverter extends DTOBuilder
{
    function __construct(EntityProto $proto)
    {
        parent::__construct($proto);
    }

    /**
     * @return ObjectGetter
     **/
    protected function getGetter($object)
    {
        return new ObjectGetter($this->proto, $object);
    }

    /**
     * @return DTOSetter
     **/
    protected function getSetter(&$object)
    {
        return new DTOSetter($this->proto, $object);
    }
}