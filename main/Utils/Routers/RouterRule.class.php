<?php

/***************************************************************************
 *   Copyright (C) 2008 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
interface RouterRule
{
    /**
     * Matches a user submitted path with parts defined by a map.
     * Assigns and returns an array of variables on a successful match.
     *
     * @return array An array of assigned values or empty array() on a mismatch
     **/
    public function match(HttpRequest $request);

    public function assembly(
        array $data = [],
        $reset = false,
        $encode = false
    );
}

