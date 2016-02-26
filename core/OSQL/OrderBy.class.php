<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup OSQL
 * @ingroup Module
 **/
class OrderBy extends FieldTable implements MappableObject
{
    /** @var null|Ternary */
    private $direction = null;
    /** @var null|Ternary */
    private $nulls = null;

    /**
     * OrderBy constructor.
     * @param $field
     */
    public function __construct($field)
    {
        parent::__construct($field);

        $this->direction = new Ternary(null);
        $this->nulls = new Ternary(null);
    }


    /**
     * @see __clone
     */
    public function __clone()
    {
        $this->direction = clone $this->direction;
        $this->nulls = clone $this->nulls;
    }

    /**
     * @return OrderBy
     */
    public function nullsFirst() : OrderBy
    {
        $this->nulls->setTrue();
        return $this;
    }

    /**
     * @return OrderBy
     */
    public function nullsLast() : OrderBy
    {
        $this->nulls->setFalse();
        return $this;
    }

    /**
     * @return null
     * @throws WrongStateException
     */
    public function isNullsFirst()
    {
        return $this->nulls->decide(true, false, true);
    }

    /**
     * @return OrderBy
     */
    public function invert() : OrderBy
    {
        return
            $this->isAsc()
                ? $this->desc()
                : $this->asc();
    }

    /**
     * @return null
     * @throws WrongStateException
     */
    public function isAsc()
    {
        return $this->direction->decide(true, false, true);
    }

    /**
     * @return OrderBy
     */
    public function desc() : OrderBy
    {
        $this->direction->setFalse();
        return $this;
    }

    /**
     * @return OrderBy
     */
    public function asc() : OrderBy
    {
        $this->direction->setTrue();
        return $this;
    }

    /**
     * @param ProtoDAO $dao
     * @param JoinCapableQuery $query
     * @return OrderBy
     */
    public function toMapped(ProtoDAO $dao, JoinCapableQuery $query) : OrderBy
    {
        $order = new self($dao->guessAtom($this->field, $query));

        if (!$this->nulls->isNull()) {
            $order->setNullsFirst($this->nulls->getValue());
        }

        if (!$this->direction->isNull()) {
            $order->setDirection($this->direction->getValue());
        }

        return $order;
    }

    /**
     * @param $nullsFirst
     * @return OrderBy
     */
    public function setNullsFirst($nullsFirst) : OrderBy
    {
        $this->nulls->setValue($nullsFirst);
        return $this;
    }

    /**
     * @param $direction
     * @return OrderBy
     */
    public function setDirection($direction) : OrderBy
    {
        $this->direction->setValue($direction);
        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return string
     * @throws WrongStateException
     */
    public function toDialectString(Dialect $dialect) : string
    {
        if (
            $this->field instanceof SelectQuery
            || $this->field instanceof LogicalObject
        ) {
            $result = '(' . $dialect->fieldToString($this->field) . ')';
        } else {
            $result = parent::toDialectString($dialect);
        }

        $result .=
            $this->direction->decide(' ASC', ' DESC')
            . $this->nulls->decide(' NULLS FIRST', ' NULLS LAST');

        return $result;
    }

    /**
     * @return null
     */
    public function getFieldName()
    {
        if ($this->field instanceof DBField) {
            return $this->field->getField();
        } else {
            return $this->field;
        }
    }
}