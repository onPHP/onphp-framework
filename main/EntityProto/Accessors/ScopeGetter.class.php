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
final class ScopeGetter extends PrototypedGetter
{
    public function get($name)
    {
        if (!isset($this->mapping[$name]))
            throw new WrongArgumentException(
                "knows nothing about property '{$name}'"
            );

        $primitive = $this->mapping[$name];

        $key = $primitive->getName();

        return
            isset($this->object[$key])
                ? $this->object[$key]
                : null;
    }
}