<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * @ingroup Logic
 **/
class PrefixUnaryExpression implements LogicalObject, MappableObject
{
    const
        NOT = 'NOT',
        MINUS = '-';

    /** @var null  */
    private $subject = null;
    /** @var null  */
    private $logic = null;
    /** @var bool  */
    private $brackets = true;

    /**
     * PrefixUnaryExpression constructor.
     * @param $logic
     * @param $subject
     */
    public function __construct($logic, $subject)
    {
        $this->subject = $subject;
        $this->logic = $logic;
    }

    /**
     * @param $subject
     * @param $logic
     * @return PrefixUnaryExpression
     */
    public static function create($subject, $logic)
    {
        return new self($subject, $logic);
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect)
    {
        $sql = $dialect->logicToString($this->logic)
            . ' ' . $dialect->toFieldString($this->subject);

        return $this->brackets ? "({$sql})" : $sql;
    }

    /**
     * @param ProtoDAO $dao
     * @param JoinCapableQuery $query
     * @return PrefixUnaryExpression
     */
    public function toMapped(ProtoDAO $dao, JoinCapableQuery $query) : PrefixUnaryExpression
    {
        $expression = new self(
            $this->logic,
            $dao->guessAtom($this->subject, $query)
        );
        return $expression->noBrackets($this->brackets);
    }

    /**
     * @param bool $noBrackets
     * @return PrefixUnaryExpression
     */
    public function noBrackets($noBrackets = true) : PrefixUnaryExpression
    {
        $this->brackets = !$noBrackets;
        return $this;
    }

    /**
     * @param Form $form
     * @return bool
     * @throws UnsupportedMethodException
     * @throws WrongArgumentException
     */
    public function toBoolean(Form $form) : bool
    {
        Assert::isTrue($this->brackets, 'brackets must be enabled');
        $subject = $form->toFormValue($this->subject);

        switch ($this->logic) {
            case self::NOT :
                return false === $subject;

            default:

                throw new UnsupportedMethodException(
                    "'{$this->logic}' doesn't supported yet"
                );
        }
    }
}
