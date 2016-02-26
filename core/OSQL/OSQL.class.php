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
class OSQL
{
    /**
     *
     * @return SelectQuery
     **/
    public function select()
    {
        return new SelectQuery();
    }

    /**
     * @return InsertQuery
     **/
    public function insert()
    {
        return new InsertQuery();
    }

    /**
     * @param null $table
     * @return UpdateQuery
     */
    public function update($table = null)
    {
        return new UpdateQuery($table);
    }

    /**
     * @return DeleteQuery
     */
    public function delete()
    {
        return new DeleteQuery();
    }

    /**
     * @param null $whom
     * @return TruncateQuery
     */
    public function truncate($whom = null)
    {
        return new TruncateQuery($whom);
    }

    /**
     * @param DBTable $table
     * @return CreateTableQuery
     */
    public function createTable(DBTable $table)
    {
        return new CreateTableQuery($table);
    }

    /**
     * @param $name
     * @param bool $cascade
     * @return DropTableQuery
     */
    public function dropTable($name, $cascade = false)
    {
        return new DropTableQuery($name, $cascade);
    }
}