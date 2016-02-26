<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Projections
 **/
class GroupByClassProjection extends ClassProjection
{
    function __construct($class)
    {
        parent::__construct($class);
    }

    /* void */
    protected function subProcess(
        JoinCapableQuery $query, DBField $field
    )
    {
        /**@var SelectQuery|UpdateQuery $query */
        $query
            ->groupBy($field);
    }
}