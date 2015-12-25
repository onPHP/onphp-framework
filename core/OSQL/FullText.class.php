<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * Base for all full-text stuff.
 *
 * @ingroup OSQL
 * @ingroup Module
 **/
abstract class FullText
    implements DialectString, MappableObject, LogicalObject
{
    /** @var null  */
    protected $logic = null;
    /** @var DBField|null  */
    protected $field = null;
    /** @var null  */
    protected $words = null;

    /**
     * FullText constructor.
     * @param $field
     * @param $words
     * @param $logic
     */
    public function __construct($field, $words, $logic)
    {
        if (is_string($field)) {
            $field = new DBField($field);
        }

        Assert::isArray($words);

        $this->field = $field;
        $this->words = $words;
        $this->logic = $logic;
    }

    /**
     * @param ProtoDAO $dao
     * @param JoinCapableQuery $query
     * @return mixed
     */
    public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
    {
        return new $this(
            $dao->guessAtom($this->field, $query, $dao->getTable()),
            $this->words,
            $this->logic
        );
    }

    /**
     * @param Form $form
     * @throws UnsupportedMethodException
     */
    public function toBoolean(Form $form)
    {
        throw new UnsupportedMethodException();
    }
}