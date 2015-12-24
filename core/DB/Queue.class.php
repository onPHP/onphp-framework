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
 * OSQL's queries queue.
 *
 * @see OSQL
 *
 * @ingroup DB
 *
 * @todo introduce DBs without multi-query support handling
 **/
final class Queue implements Query
{
    private $queue = [];

    /**
     * @deprecated
     * @return Queue
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return sha1(serialize($this->queue));
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
     * @return array
     */
    public function getQueue() : array
    {
        return $this->queue;
    }

    /**
     * @return Queue
     **/
    public function add(Query $query)
    {
        $this->queue[] = $query;

        return $this;
    }

    /**
     * @param Query $query
     * @return Queue
     * @throws MissingElementException
     */
    public function remove(Query $query) : Queue
    {
        if (!$id = array_search($query, $this->queue)) {
            throw new MissingElementException();
        }

        unset($this->queue[$id]);

        return $this;
    }

    /**
     * @param DB $db
     * @return Queue
     */
    public function flush(DB $db) : Queue
    {
        return $this->run($db)->drop();
    }

    /**
     * @return Queue
     */
    public function drop() : Queue
    {
        $this->queue = [];

        return $this;
    }

    /**
     * @param DB $db
     * @return Queue
     */
    public function run(DB $db) : Queue
    {
        $db->queryRaw($this->toDialectString($db->getDialect()));

        return $this;
    }

    // to satisfy Query interface
    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $out = [];

        foreach ($this->queue as $query) {
            $out[] = $query->toDialectString($dialect);
        }

        return implode(";\n", $out);
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return $this->toDialectString(ImaginaryDialect::me());
    }
}

?>