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
 * Wrapper around given childs of LogicalObject with custom logic-glue's.
 *
 * @ingroup Logic
 **/
final class LogicalChain extends SQLChain
{
    /**
     * @param $args
     * @param $logic
     * @return LogicalChain
     * @throws WrongArgumentException
     */
    public static function block($args, $logic) : LogicalChain
    {
        Assert::isTrue(
            ($logic == BinaryExpression::EXPRESSION_AND)
            || ($logic == BinaryExpression::EXPRESSION_OR),

            "unknown logic '{$logic}'"
        );

        $logicalChain = new self;

        foreach ($args as $arg) {
            if (
                !$arg instanceof LogicalObject
                && !$arg instanceof SelectQuery
            ) {
                throw new WrongArgumentException(
                    'unsupported object type: ' . get_class($arg)
                );
            }

            $logicalChain->exp($arg, $logic);
        }

        return $logicalChain;
    }

    /**
     * @param LogicalObject $exp
     * @return SQLChain
     */
    public function expAnd(LogicalObject $exp) : SQLChain
    {
        return $this->exp($exp, BinaryExpression::EXPRESSION_AND);
    }

    /**
     * @param LogicalObject $exp
     * @return SQLChain
     */
    public function expOr(LogicalObject $exp) : SQLChain
    {
        return $this->exp($exp, BinaryExpression::EXPRESSION_OR);
    }

    /**
     * @param Form $form
     * @return bool
     * @throws WrongArgumentException
     */
    public function toBoolean(Form $form) : bool
    {
        $chain = &$this->chain;

        $size = count($chain);

        if (!$size) {
            throw new WrongArgumentException(
                'empty chain can not be calculated'
            );
        } elseif ($size == 1) {
            return $chain[0]->toBoolean($form);
        } else { // size > 1
            $out = $chain[0]->toBoolean($form);

            for ($i = 1; $i < $size; ++$i) {
                $out =
                    self::calculateBoolean(
                        $this->logic[$i],
                        $out,
                        $chain[$i]->toBoolean($form)
                    );
            }

            return $out;
        }

        Assert::isUnreachable();
    }

    /**
     * @param $logic
     * @param $left
     * @param $right
     * @return bool
     * @throws WrongArgumentException
     */
    private static function calculateBoolean($logic, $left, $right) : bool
    {
        switch ($logic) {
            case BinaryExpression::EXPRESSION_AND:
                return $left && $right;

            case BinaryExpression::EXPRESSION_OR:
                return $left || $right;

            default:
                throw new WrongArgumentException(
                    "unknown logic - '{$logic}'"
                );
        }

        Assert::isUnreachable();
    }
}