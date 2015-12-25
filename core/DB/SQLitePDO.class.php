<?php
/***************************************************************************
 *   Copyright (C) 2012 by Aleksey S. Denisov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * SQLitePDO DB connector.
 *
 * @see http://www.sqlite.org/
 * @see http://www.php.net/manual/en/ref.pdo-sqlite.php
 *
 * @ingroup DB
 **/
class SQLitePDO extends Sequenceless
{

    const ERROR_CONSTRAINT = 19;
    /**
     * @var PDO
     */
    protected $link = null;

    /**
     * @return $this
     * @throws DatabaseException
     */
    public function connect()
    {
        try {
            $this->link = new PDO(
                "sqlite:{$this->basename}",
                '',
                '',
                [PDO::ATTR_PERSISTENT => $this->persistent]
            );
        } catch (PDOException $e) {
            throw new DatabaseException(
                'can not open SQLitePDO base: '
                . $e->getMessage()
            );
        }
        $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this;
    }

    /**
     * @return SQLitePDO
     **/
    public function disconnect()
    {
        if ($this->link) {
            $this->link = null;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isConnected() : bool
    {
        return $this->link !== null;
    }

    /**
     * @throws UnsupportedMethodException
     */
    public function setDbEncoding()
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param $queryString
     * @return PDOStatement
     */
    public function queryRaw($queryString)
    {
        try {
            return $this->link->query($queryString);
        } catch (PDOException $e) {
            $code = $e->getCode();

            if ($code == self::ERROR_CONSTRAINT) {
                $exc = 'DuplicateObjectException';
            } else {
                $exc = 'DatabaseException';
            }

            throw new $exc($e->getMessage() . ': ' . $queryString);
        }
    }

    /**
     * Same as query, but returns number of affected rows
     * Returns number of affected rows in insert/update queries
     *
     * @param Query $query
     * @return int
     * @throws WrongArgumentException
     */
    public function queryCount(Query $query)
    {
        $res = $this->queryNull($query);
        /* @var $res PDOStatement */

        return $res->rowCount();
    }

    /**
     * @param Query $query
     * @return mixed|null
     * @throws TooManyRowsException
     */
    public function queryRow(Query $query)
    {
        $res = $this->query($query);
        /* @var $res PDOStatement */

        $array = $res->fetchAll(PDO::FETCH_ASSOC);
        if (count($array) > 1) {
            throw new TooManyRowsException(
                'query returned too many rows (we need only one)'
            );
        } elseif (count($array) == 1) {
            return reset($array);
        } else {
            return null;
        }
    }

    /**
     * @param Query $query
     * @return array|null
     */
    public function queryColumn(Query $query)
    {
        $res = $this->query($query);
        /* @var $res PDOStatement */

        $resArray = $res->fetchAll(PDO::FETCH_ASSOC);
        if ($resArray) {
            $array = [];
            foreach ($resArray as $row) {
                $array[] = reset($row);
            }

            return $array;
        } else {
            return null;
        }
    }

    /**
     * @param Query $query
     * @return null
     */
    public function querySet(Query $query)
    {
        $res = $this->query($query);
        /* @var $res PDOStatement */

        return $res->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * @return bool
     */
    public function hasQueue()
    {
        return false;
    }

    /**
     * @param $table
     * @throws UnimplementedFeatureException
     */
    public function getTableInfo($table)
    {
        throw new UnimplementedFeatureException();
    }

    /**
     * @return string
     */
    protected function getInsertId() : string
    {
        return $this->link->lastInsertId();
    }

    /**
     * @return LitePDODialect
     **/
    protected function spawnDialect()
    {
        return new LitePDODialect();
    }
}