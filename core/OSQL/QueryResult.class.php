<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Holder for query's execution information.
 *
 * @ingroup OSQL
 **/
class QueryResult implements Identifiable
{
    /** @var array  */
    private $list = [];

    /** @var int  */
    private $count = 0;
    /** @var int  */
    private $affected = 0;

    /** @var null  */
    private $query = null;


    /**
     * @return string
     */
    public function getId() : string
    {
        return '_result_' . $this->getQuery()->getId();
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
     * @return SelectQuery
     **/
    public function getQuery() : SelectQuery
    {
        return $this->query;
    }

    /**
     * @param SelectQuery $query
     * @return QueryResult
     */
    public function setQuery(SelectQuery $query) : QueryResult
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return array
     */
    public function getList() : array
    {
        return $this->list;
    }

    /**
     * @param array $list
     * @return QueryResult
     */
    public function setList(array $list) : QueryResult
    {
        $this->list = $list;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount() : int
    {
        return $this->count;
    }

    /**
     * @param $count
     * @return QueryResult
     */
    public function setCount($count) : QueryResult
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getAffected() : int
    {
        return $this->affected;
    }

    /**
     * @param $affected
     * @return QueryResult
     */
    public function setAffected($affected) : QueryResult
    {
        $this->affected = $affected;

        return $this;
    }
}
