<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Factory for OSQL's queries.
 *
 * @ingroup OSQL
 *
 * @see http://onphp.org/examples.OSQL.en.html
 **/
class OSQL extends StaticFactory
{
    /**
     * @deprecated
     *
     * @return SelectQuery
     **/
    public static function select()
    {
        return new SelectQuery();
    }

    /**
     * @return InsertQuery
     **/
    public static function insert()
    {
        return new InsertQuery();
    }

    /**
     * @param null $table
     * @return UpdateQuery
     */
    public static function update($table = null)
    {
        return new UpdateQuery($table);
    }

    /**
     * @return DeleteQuery
     */
    public static function delete()
    {
        return new DeleteQuery();
    }

    /**
     * @param null $whom
     * @return TruncateQuery
     */
    public static function truncate($whom = null)
    {
        return new TruncateQuery($whom);
    }

    /**
     * @param DBTable $table
     * @return CreateTableQuery
     */
    public static function createTable(DBTable $table)
    {
        return new CreateTableQuery($table);
    }

    /**
     * @param $name
     * @param bool $cascade
     * @return DropTableQuery
     */
    public static function dropTable($name, $cascade = false)
    {
        return new DropTableQuery($name, $cascade);
    }
}