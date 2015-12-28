<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Factory for various childs of LogicalObjects
 *
 * @ingroup Logic
 **/
class Expression extends StaticFactory
{
    /**
     * @param $left
     * @param $right
     * @return BinaryExpression
     */
    public static function expAnd($left, $right) : BinaryExpression
    {
        return new BinaryExpression(
            $left, $right, BinaryExpression::EXPRESSION_AND
        );
    }

    /**
     * @param $left
     * @param $right
     * @return BinaryExpression
     */
    public static function expOr($left, $right) : BinaryExpression
    {
        return new BinaryExpression(
            $left, $right, BinaryExpression::EXPRESSION_OR
        );
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function eq($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::EQUALS);
    }

    /**
     * @param $field
     * @param Identifiable $object
     * @return BinaryExpression
     */
    public static function eqId($field, Identifiable $object) : BinaryExpression
    {
        return self::eq($field, new DBValue($object->getId()));
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function notEq($field, $value) : BinaryExpression
    {
        return new BinaryExpression(
            $field, $value, BinaryExpression::NOT_EQUALS
        );
    }

    /**
     * greater than
     *
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function gt($field, $value) : BinaryExpression
    {
        return new BinaryExpression(
            $field, $value, BinaryExpression::GREATER_THAN
        );
    }

    /**
     * greater than or equals
     *
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function gtEq($field, $value) : BinaryExpression
    {
        return new BinaryExpression(
            $field, $value, BinaryExpression::GREATER_OR_EQUALS
        );
    }

    /**
     * lower than
     *
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function lt($field, $value) : BinaryExpression
    {
        return new BinaryExpression(
            $field, $value, BinaryExpression::LOWER_THAN
        );
    }

    /**
     * lower than or equals
     *
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function ltEq($field, $value) : BinaryExpression
    {
        return new BinaryExpression(
            $field, $value, BinaryExpression::LOWER_OR_EQUALS
        );
    }

    /**
     * @param $field
     * @return PostfixUnaryExpression
     */
    public static function notNull($field) : PostfixUnaryExpression
    {
        return new PostfixUnaryExpression($field, PostfixUnaryExpression::IS_NOT_NULL);
    }

    /**
     * @param $field
     * @return PostfixUnaryExpression
     */
    public static function isNull($field) : PostfixUnaryExpression
    {
        return new PostfixUnaryExpression($field, PostfixUnaryExpression::IS_NULL);
    }

    /**
     * @param $field
     * @return PostfixUnaryExpression
     */
    public static function isTrue($field) : PostfixUnaryExpression
    {
        return new PostfixUnaryExpression($field, PostfixUnaryExpression::IS_TRUE);
    }

    /**
     * @param $field
     * @return PostfixUnaryExpression
     */
    public static function isFalse($field) : PostfixUnaryExpression
    {
        return new PostfixUnaryExpression($field, PostfixUnaryExpression::IS_FALSE);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function like($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::LIKE);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function notLike($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::NOT_LIKE);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function ilike($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::ILIKE);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function notIlike($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::NOT_ILIKE);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function similar($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::SIMILAR_TO);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function notSimilar($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::NOT_SIMILAR_TO);
    }

    /**
     * @param $field
     * @param $value
     * @return EqualsLowerExpression
     */
    public static function eqLower($field, $value) : EqualsLowerExpression
    {
        return new EqualsLowerExpression($field, $value);
    }

    /**
     * @param $field
     * @param $left
     * @param $right
     * @return LogicalBetween
     */
    public static function between($field, $left, $right) : LogicalBetween
    {
        return new LogicalBetween($field, $left, $right);
    }

    /**
     * {,not}in handles strings, arrays and SelectQueries
     *
     * @return LogicalObject
     **/
    /**
     * @param $field
     * @param $value
     * @return LogicalObject
     */
    public static function in($field, $value) : LogicalObject
    {
        if (is_numeric($value) && $value == (int)$value)
            return self::eq($field, $value);
        elseif (is_array($value) && count($value) == 1)
            return self::eq($field, current($value));
        else {
            return new InExpression(
                $field, $value, InExpression::IN
            );
        }
    }

    /**
     * @param $field
     * @param $value
     * @return LogicalObject
     */
    public static function notIn($field, $value) : LogicalObject
    {
        if (is_numeric($value) && $value == (int)$value)
            return self::notEq($field, $value);
        elseif (is_array($value) && count($value) == 1)
            return self::notEq($field, current($value));
        else {
            return new InExpression(
                $field, $value, InExpression::NOT_IN
            );
        }
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function add($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::ADD);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function sub($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::SUBSTRACT);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function mul($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::MULTIPLY);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function div($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::DIVIDE);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function mod($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, BinaryExpression::MOD);
    }

    /**
     * @param $field
     * @param $wordsList
     * @return FullTextSearch
     */
    public static function fullTextAnd($field, $wordsList) : FullTextSearch
    {
        return new FullTextSearch($field, $wordsList, DB::FULL_TEXT_AND);
    }

    /**
     * @param $field
     * @param $wordsList
     * @return FullTextSearch
     */
    public static function fullTextOr($field, $wordsList) : FullTextSearch
    {
        return new FullTextSearch($field, $wordsList, DB::FULL_TEXT_OR);
    }

    /**
     * @param $field
     * @param $wordsList
     * @return FullTextRank
     */
    public static function fullTextRankOr($field, $wordsList) : FullTextRank
    {
        return new FullTextRank($field, $wordsList, DB::FULL_TEXT_OR);
    }

    /**
     * @param $field
     * @param $wordsList
     * @return FullTextRank
     */
    public static function fullTextRankAnd($field, $wordsList) : FullTextRank
    {
        return new FullTextRank($field, $wordsList, DB::FULL_TEXT_AND);
    }

    /**
     * @param array ...$args
     * @return BinaryExpression
     */
    public static function orBlock(...$args) : BinaryExpression
    {
        return self::block(
            $args,
            BinaryExpression::EXPRESSION_OR
        );
    }

    /**
     * @param array ...$args
     * @return LogicalChain
     */
    public static function andBlock(...$args) : LogicalChain
    {
        return self::block(
            $args,
            BinaryExpression::EXPRESSION_AND
        );
    }

    /**
     * @return LogicalChain
     */
    public static function chain() : LogicalChain
    {
        return new LogicalChain();
    }

    /**
     * @param $field
     * @return PrefixUnaryExpression
     */
    public static function not($field) : PrefixUnaryExpression
    {
        return new PrefixUnaryExpression(PrefixUnaryExpression::NOT, $field);
    }

    /**
     * @param $field
     * @return PrefixUnaryExpression
     */
    public static function minus($field) : PrefixUnaryExpression
    {
        return new PrefixUnaryExpression(PrefixUnaryExpression::MINUS, $field);
    }

    /**
     * @param $range
     * @param $ip
     * @return Ip4ContainsExpression
     */
    public static function containsIp($range, $ip) : Ip4ContainsExpression
    {
        return new Ip4ContainsExpression($range, $ip);
    }

    /**
     * @param $args
     * @param $logic
     * @return LogicalChain
     * @throws WrongArgumentException
     */
    private static function block($args, $logic) : LogicalChain
    {
        return LogicalChain::block($args, $logic);
    }
}