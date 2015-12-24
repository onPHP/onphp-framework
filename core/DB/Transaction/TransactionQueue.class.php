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
 * Transaction-wrapped queries queue.
 *
 * @see Queue
 *
 * @ingroup Transaction
 **/
class TransactionQueue extends BaseTransaction implements Query
{
    private $queue = null;

    /**
     * TransactionQueue constructor.
     * @param DB $db
     */
    public function __construct(DB $db)
    {
        parent::__construct($db);
        $this->queue = new Queue();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return sha1(serialize($this));
    }

    /**
     * @param $id
     * @throws UnsupportedMethodException
     */
    public function setId($id)
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @return TransactionQueue
     **/
    public function add(Query $query)
    {
        $this->queue->add($query);

        return $this;
    }

    /**
     * @throws DatabaseException
     * @return TransactionQueue
     **/
    public function flush() : TransactionQueue
    {
        try {
            $this->db->queryRaw($this->getBeginString());
            $this->queue->flush($this->db);
            $this->db->queryRaw("commit;\n");
        } catch (DatabaseException $e) {
            $this->db->queryRaw("rollback;\n");
            throw $e;
        }

        return $this;
    }

    // to satisfy Query interface
    public function toDialectString(Dialect $dialect)
    {
        return $this->queue->toDialectString($dialect);
    }

    public function toString()
    {
        return $this->queue->toDialectString(ImaginaryDialect::me());
    }
}
