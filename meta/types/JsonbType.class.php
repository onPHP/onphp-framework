<?php
/***************************************************************************
 *   Copyright (C) 2015 Anton Gurov                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/**
 * @ingroup Types
 * @see http://www.postgresql.org/docs/9.4/static/datatype-json.html
 **/
class JsonbType extends JsonType
{
    public function getPrimitiveName()
    {
        return 'jsonb';
    }
    public function toColumnType()
    {
        return '(new DataType(DataType::JSONB))';
    }
}
?>