<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Database transaction implementation.
 *
 * @ingroup Transaction
 **/
class DBTransaction extends BaseTransaction
{
    /** @var bool */
    private $started = false;

    /**
     * @see destruct
     */
    public function __destruct()
    {
        if ($this->isStarted()) {
            $this->db->queryRaw("rollback;\n");
        }
    }

    /**
     * @return bool
     */
    public function isStarted() : bool
    {
        return $this->started;
    }

    /**
     * @param DB $db
     * @return DBTransaction
     * @throws WrongStateException
     */
    public function setDB(DB $db) : DBTransaction
    {
        if ($this->isStarted()) {
            throw new WrongStateException(
                'transaction already started, can not switch to another db'
            );
        }

        parent::setDB($db);

        return $this;
    }

    /**
     * @param Query $query
     * @return DBTransaction
     * @throws WrongArgumentException
     */
    public function add(Query $query) : DBTransaction
    {
        if (!$this->isStarted()) {
            $this->db->queryRaw($this->getBeginString());
            $this->started = true;
        }

        $this->db->queryNull($query);

        return $this;
    }

    /**
     * @return DBTransaction
     * @throws DatabaseException
     */
    public function flush() : DBTransaction
    {
        $this->started = false;

        try {
            $this->db->queryRaw("commit;\n");
        } catch (DatabaseException $e) {
            $this->db->queryRaw("rollback;\n");
            throw $e;
        }

        return $this;
    }
}
