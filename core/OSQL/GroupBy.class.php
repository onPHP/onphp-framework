<?php
/****************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * @ingroup OSQL
 * @ingroup Module
 **/
final class GroupBy extends FieldTable implements MappableObject
{
    /**
     * @deprecated
     *
     * @return GroupBy
     **/
    public static function create($field)
    {
        return new self($field);
    }

    /**
     * @param ProtoDAO $dao
     * @param JoinCapableQuery $query
     * @return GroupBy
     */
    public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
    {
        return new self($dao->guessAtom($this->field, $query));
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        if (
            $this->field instanceof SelectQuery
            || $this->field instanceof LogicalObject
        ) {
            return '(' . $dialect->fieldToString($this->field) . ')';
        } else {
            return parent::toDialectString($dialect);
        }
    }
}