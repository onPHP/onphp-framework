<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup OSQL
 **/
class DBColumn implements SQLTableName
{
    /** @var DataType|null */
    private $type = null;
    /** @var null */
    private $name = null;

    /** @var null */
    private $table = null;
    /** @var null */
    private $default = null;

    /** @var null */
    private $reference = null;
    /** @var null */
    private $onUpdate = null;
    /** @var null */
    private $onDelete = null;

    /** @var bool */
    private $primary;
    /** @var bool */
    private $sequenced;

    /**
     * DBColumn constructor.
     * @param DataType $type
     * @param $name
     */
    public function __construct(DataType $type, $name)
    {
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * @deprecated
     * @return DBColumn
     **/
    public static function create(DataType $type, $name)
    {
        return new self($type, $name);
    }

    /**
     * @return DataType|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return DBTable
     **/
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param DBTable $table
     * @return DBColumn
     */
    public function setTable(DBTable $table) : DBColumn
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimaryKey()
    {
        return $this->primary;
    }

    /**
     * @param bool $primary
     * @return DBColumn
     */
    public function setPrimaryKey(bool $primary = false) : DBColumn
    {
        $this->primary = true === $primary;

        return $this;
    }

    /**
     * @return null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return DBColumn
     **/
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param DBColumn $column
     * @param ForeignChangeAction $onDelete
     * @param ForeignChangeAction $onUpdate
     * @return DBColumn
     * @throws WrongArgumentException
     */
    public function setReference(DBColumn $column, $onDelete = null, $onUpdate = null) : DBColumn
    {
        Assert::isTrue(
            (
                (null === $onDelete)
                || $onDelete instanceof ForeignChangeAction
            )
            && (
                (null === $onUpdate)
                || $onUpdate instanceof ForeignChangeAction
            )
        );

        $this->reference = $column;
        $this->onDelete = $onDelete;
        $this->onUpdate = $onUpdate;

        return $this;
    }

    /**
     * @return DBColumn
     */
    public function dropReference() : DBColumn
    {
        $this->reference = null;
        $this->onDelete = null;
        $this->onUpdate = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasReference() : bool
    {
        return ($this->reference !== null);
    }

    /**
     * @param bool $auto
     * @return DBColumn
     */
    public function setAutoincrement(bool $auto = false) : DBColumn
    {
        $this->sequenced = (true === $auto);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoincrement()
    {
        return $this->sequenced;
    }

    /**
     * @param Dialect $dialect
     * @return string
     * @throws WrongStateException
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $out =
            $dialect->quoteField($this->name) . ' '
            . $this->type->toDialectString($dialect);

        if (null !== $this->default) {

            if ($this->type->getId() == DataType::BOOLEAN) {
                $default = $this->default
                    ? $dialect->literalToString(Dialect::LITERAL_TRUE)
                    : $dialect->literalToString(Dialect::LITERAL_FALSE);
            } else {
                $default = $dialect->valueToString($this->default);
            }

            $out .= ' DEFAULT ' . ($default);
        }

        if ($this->reference) {

            $table = $this->reference->getTable()->getName();
            $column = $this->reference->getName();

            $out .=
                " REFERENCES {$dialect->quoteTable($table)}"
                . "({$dialect->quoteField($column)})";

            if ($this->onDelete) {
                $out .= ' ON DELETE ' . $this->onDelete->toString();
            }

            if ($this->onUpdate) {
                $out .= ' ON UPDATE ' . $this->onUpdate->toString();
            }
        }

        return $out;
    }
}
