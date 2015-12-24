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
 * @ingroup Logic
 **/
final class BinaryExpression implements LogicalObject, MappableObject
{
    const
        EQUALS = '=',
        NOT_EQUALS = '!=',
        EXPRESSION_AND = 'AND',
        EXPRESSION_OR = 'OR',
        GREATER_THAN = '>',
        GREATER_OR_EQUALS = '>=',
        LOWER_THAN = '<',
        LOWER_OR_EQUALS = '<=',
        LIKE = 'LIKE',
        NOT_LIKE = 'NOT LIKE',
        ILIKE = 'ILIKE',
        NOT_ILIKE = 'NOT ILIKE',
        SIMILAR_TO = 'SIMILAR TO',
        NOT_SIMILAR_TO = 'NOT SIMILAR TO',
        ADD = '+',
        SUBSTRACT = '-',
        MULTIPLY = '*',
        DIVIDE = '/',
        MOD = '%';

    /** @var null  */
    private $left = null;

    /** @var null  */
    private $right = null;

    /** @var null  */
    private $logic = null;

    /** @var bool  */
    private $brackets = true;

    /**
     * @deprecated
     *
     * @param $left
     * @param $right
     * @param $logic
     * @return BinaryExpression
     */
    public static function create($left, $right, $logic)
    {
        return new self($left, $right, $logic);
    }

    /**
     * BinaryExpression constructor.
     * @param $left
     * @param $right
     * @param $logic
     */
    public function __construct($left, $right, $logic)
    {
        $this->left = $left;
        $this->right = $right;
        $this->logic = $logic;
    }

    /**
     * @return null
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @return null
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @return null
     */
    public function getLogic()
    {
        return $this->logic;
    }

    /**
     * @param boolean $noBrackets
     * @return BinaryExpression
     */
    public function noBrackets($noBrackets = true)
    {
        $this->brackets = !$noBrackets;
        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $sql = $dialect->toFieldString($this->left)
            . ' ' . $dialect->logicToString($this->logic) . ' '
            . $dialect->toValueString($this->right);
        return $this->brackets ? "({$sql})" : $sql;
    }

    /**
     * @param ProtoDAO $dao
     * @param JoinCapableQuery $query
     * @return BinaryExpression
     */
    public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
    {
        $expression = new self(
            $dao->guessAtom($this->left, $query),
            $dao->guessAtom($this->right, $query),
            $this->logic
        );

        return $expression->noBrackets(!$this->brackets);
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
        $left = $form->toFormValue($this->left);
        $right = $form->toFormValue($this->right);

        $both =
            (null !== $left)
            && (null !== $right);

        switch ($this->logic) {
            case self::EQUALS:
                return $both && ($left == $right);

            case self::NOT_EQUALS:
                return $both && ($left != $right);

            case self::GREATER_THAN:
                return $both && ($left > $right);

            case self::GREATER_OR_EQUALS:
                return $both && ($left >= $right);

            case self::LOWER_THAN:
                return $both && ($left < $right);

            case self::LOWER_OR_EQUALS:
                return $both && ($left <= $right);

            case self::EXPRESSION_AND:
                return $both && ($left && $right);

            case self::EXPRESSION_OR:
                return $both && ($left || $right);

            case self::ADD:
                return $both && ($left + $right);

            case self::SUBSTRACT:
                return $both && ($left - $right);

            case self::MULTIPLY:
                return $both && ($left * $right);

            case self::DIVIDE:
                return $both && $right && ($left / $right);

            case self::MOD:
                return $both && $right && ($left % $right);

            default:
                throw new UnsupportedMethodException(
                    "'{$this->logic}' doesn't supported yet"
                );
        }
    }
}