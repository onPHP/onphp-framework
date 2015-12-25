<?php
/****************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * @ingroup OSQL
 **/
final class QueryCombination extends QueryIdentification implements DialectString
{
    /** @var null|Query  */
    private $left = null;
    /** @var null|Query  */
    private $right = null;
    /** @var null  */
    private $logic = null;

    /** @var null  */
    private $limit = null;
    /** @var null  */
    private $offset = null;

    /** @var null|OrderChain  */
    private $order = null;

    /**
     * QueryCombination constructor.
     * @param Query $left
     * @param Query $right
     * @param $logic
     */
    public function __construct(Query $left, Query $right, $logic) {
        $this->left = $left;
        $this->right = $right;
        $this->logic = $logic;
        $this->order = new OrderChain();
    }

    /**
     * @see __clone
     */
    public function __clone()
    {
        $this->left = clone $this->left;
        $this->right = clone $this->right;
        $this->order = clone $this->order;
    }

    /**
     * @return null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param null $limit
     * @param null $offset
     * @return QueryCombination
     * @throws WrongArgumentException
     */
    public function limit($limit = null, $offset = null) : QueryCombination
    {
        if ($limit !== null) {
            Assert::isPositiveInteger($limit, 'invalid limit specified');
        }

        if ($offset !== null) {
            Assert::isInteger($offset, 'invalid offset specified');
        }

        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return QueryCombination
     */
    public function dropOrder() : QueryCombination
    {
        $this->order = new OrderChain();

        return $this;
    }

    /**
     * @param OrderChain $chain
     * @return QueryCombination
     */
    public function setOrderChain(OrderChain $chain) : QueryCombination
    {
        $this->order = $chain;

        return $this;
    }

    /**
     * @param $field
     * @return QueryCombination
     */
    public function orderBy($field) : QueryCombination
    {
        $this->order->add($field);

        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $query =
            $this->left->toDialectString($dialect)
            . " {$this->logic} "
            . $this->right->toDialectString($dialect);

        if ($this->order->getCount()) {
            $query .= ' ORDER BY ' . $this->order->toDialectString($dialect);
        }

        if ($this->limit) {
            $query .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset) {
            $query .= ' OFFSET ' . $this->offset;
        }

        return $query;
    }
}
