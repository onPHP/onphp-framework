<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * SQLite DB connector.
 *
 * you may wish to ini_set('sqlite.assoc_case', 0);
 *
 * @see http://www.sqlite.org/
 *
 * @ingroup DB
 **/
class SQLite extends Sequenceless
{
    /**
     * @return $this
     * @throws DatabaseException
     */
    public function connect()
    {
        if ($this->persistent)
            $this->link = sqlite_popen($this->basename);
        else
            $this->link = sqlite_open($this->basename);

        if (!$this->link)
            throw new DatabaseException(
                'can not open SQLite base: '
                . sqlite_error_string(sqlite_last_error($this->link))
            );

        return $this;
    }

    /**
     * @return SQLite
     **/
    public function disconnect()
    {
        if ($this->isConnected())
            sqlite_close($this->link);

        return $this;
    }

    public function isConnected()
    {
        return is_resource($this->link);
    }

    /**
     * misc
     **/

    public function setDbEncoding()
    {
        throw new UnsupportedMethodException();
    }

    /**
     * query methods
     **/

    public function queryRaw($queryString)
    {
        try {
            return sqlite_query($queryString, $this->link);
        } catch (BaseException $e) {
            $code = sqlite_last_error($this->link);

            if ($code == 19)
                $e = 'DuplicateObjectException';
            else
                $e = 'DatabaseException';

            throw new $e(
                sqlite_error_string($code) . ' - ' . $queryString,
                $code
            );
        }
    }

    /**
     * Same as query, but returns number of affected rows
     * Returns number of affected rows in insert/update queries
     **/
    public function queryCount(Query $query)
    {
        $this->queryNull($query);

        return sqlite_changes($this->link);
    }

    public function queryRow(Query $query)
    {
        $res = $this->query($query);

        if ($this->checkSingle($res)) {
            if (!$row = sqlite_fetch_array($res, SQLITE_NUM))
                return null;

            $names = $query->getFieldNames();
            $width = count($names);
            $assoc = [];

            for ($i = 0; $i < $width; ++$i)
                $assoc[$names[$i]] = $row[$i];

            return $assoc;
        } else
            return null;
    }

    public function queryColumn(Query $query)
    {
        $res = $this->query($query);

        if ($res) {
            $array = [];

            while ($row = sqlite_fetch_single($res))
                $array[] = $row;

            return $array;
        } else
            return null;
    }

    public function querySet(Query $query)
    {
        $res = $this->query($query);

        /**@var SelectQuery $query */
        if ($res) {
            $array = [];
            $names = $query->getFieldNames();
            $width = count($names);

            while ($row = sqlite_fetch_array($res, SQLITE_NUM)) {
                $assoc = [];

                for ($i = 0; $i < $width; ++$i)
                    $assoc[$names[$i]] = $row[$i];

                $array[] = $assoc;
            }

            return $array;
        } else
            return null;
    }

    public function hasQueue()
    {
        return false;
    }

    public function getTableInfo($table)
    {
        throw new UnimplementedFeatureException();
    }

    protected function getInsertId()
    {
        return sqlite_last_insert_rowid($this->link);
    }

    /**
     * @return LiteDialect
     **/
    protected function spawnDialect()
    {
        return new LiteDialect();
    }

    private function checkSingle($result)
    {
        if (sqlite_num_rows($result) > 1)
            throw new TooManyRowsException(
                'query returned too many rows (we need only one)'
            );

        return $result;
    }
}