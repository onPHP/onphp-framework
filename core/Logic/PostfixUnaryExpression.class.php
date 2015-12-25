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
class PostfixUnaryExpression implements LogicalObject, MappableObject
{
    const
        IS_NULL = 'IS NULL',
        IS_NOT_NULL = 'IS NOT NULL',

        IS_TRUE = 'IS TRUE',
        IS_FALSE = 'IS FALSE';

    /** @var null  */
    private $subject = null;
    /** @var null  */
    private $logic = null;
    /** @var bool  */
    private $brackets = true;

    /**
     * PostfixUnaryExpression constructor.
     * @param $subject
     * @param $logic
     */
    public function __construct($subject, $logic)
    {
        $this->subject = $subject;
        $this->logic = $logic;
    }

    /**
     * @deprecated
     *
     * @param $subject
     * @param $logic
     * @return PostfixUnaryExpression
     */
    public static function create($subject, $logic)
    {
        return new self($subject, $logic);
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $sql = $dialect->toFieldString($this->subject)
            . ' ' . $dialect->logicToString($this->logic);
        return $this->brackets ? "({$sql})" : $sql;
    }

    /**
     * @param ProtoDAO $dao
     * @param JoinCapableQuery $query
     * @return PostfixUnaryExpression
     */
    public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
    {
        $expression = new self(
            $dao->guessAtom($this->subject, $query),
            $this->logic
        );

        return $expression->noBrackets(!$this->brackets);
    }

    /**
     * @param bool $noBrackets
     * @return $this
     */
    public function noBrackets($noBrackets = true)
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
    public function toBoolean(Form $form)
    {
        Assert::isTrue($this->brackets, 'brackets must be enabled');
        $subject = $form->toFormValue($this->subject);

        switch ($this->logic) {
            case self::IS_NULL:
                return null === $subject;

            case self::IS_NOT_NULL:
                return null !== $subject;

            case self::IS_TRUE:
                return true === $subject;

            case self::IS_FALSE:
                return false === $subject;

            default:

                throw new UnsupportedMethodException(
                    "'{$this->logic}' doesn't supported yet"
                );
        }
    }
}