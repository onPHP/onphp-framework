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
 * @ingroup Types
 **/
class DateType extends ObjectType
{
    public function getPrimitiveName()
    {
        return 'date';
    }

    public function isGeneric()
    {
        return true;
    }

    public function toColumnType()
    {
        return '(new DataType(DataType::DATE))';
    }
}

?>