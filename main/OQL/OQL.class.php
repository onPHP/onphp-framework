<?php
/***************************************************************************
 *   Copyright (C) 2008-2009 by Vladlen Y. Koshelev                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup OQL
 **/
class OQL extends StaticFactory
{
    /**
     * @return OqlSelectQuery
     **/
    public static function select($query)
    {
        return (new OqlSelectParser())->parse($query);
    }

    /**
     * @return OqlSelectPropertiesClause
     **/
    public static function properties($clause)
    {
        return (new OqlSelectPropertiesParser())->parse($clause);
    }

    /**
     * @return OqlWhereClause
     **/
    public static function where($clause)
    {
        return (new OqlWhereParser())->parse($clause);
    }

    /**
     * @return OqlProjectionClause
     **/
    public static function groupBy($clause)
    {
        return (new OqlGroupByParser())->parse($clause);
    }

    /**
     * @return OqlOrderByClause
     **/
    public static function orderBy($clause)
    {
        return (new OqlOrderByParser())->parse($clause);
    }

    /**
     * @return OqlHavingClause
     **/
    public static function having($clause)
    {
        return (new OqlHavingParser())->parse($clause);
    }
}
