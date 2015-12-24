<?php
/***************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Logic
 * @see http://www.postgresql.org/docs/8.3/interactive/hstore.html
 **/
final class HstoreExpression extends StaticFactory
{
    const
        CONTAIN = '?',
        GET_VALUE = '->',
        LEFT_CONTAIN = '@>',
        CONCAT = '||';

    /**
     * @param $field
     * @param $key
     * @return BinaryExpression
     */
    public static function containKey($field, $key) : BinaryExpression
    {
        return new BinaryExpression($field, $key, self::CONTAIN);
    }

    /**
     * @param $field
     * @param $key
     * @return BinaryExpression
     */
    public static function getValueByKey($field, $key) : BinaryExpression
    {
        return new BinaryExpression($field, $key, self::GET_VALUE);
    }

    /**
     * @param $field
     * @param $key
     * @param $value
     * @return BinaryExpression
     */
    public static function containValue($field, $key, $value) : BinaryExpression
    {
        return new BinaryExpression($field, "{$key}=>{$value}", self::LEFT_CONTAIN);
    }

    /**
     * @param $field
     * @param $value
     * @return BinaryExpression
     */
    public static function concat($field, $value) : BinaryExpression
    {
        return new BinaryExpression($field, $value, self::CONCAT);
    }

    /**
     * @param $field
     * @param Hstore $hstore
     * @return BinaryExpression
     */
    public static function containHstore($field, Hstore $hstore) : BinaryExpression
    {
        return new BinaryExpression($field, $hstore->toString(), self::LEFT_CONTAIN);
    }

    /**
     * @param $field
     * @param array $list
     * @return BinaryExpression
     */
    public static function containValueList($field, array $list) : BinaryExpression
    {
        return
            self::containHstore(
                $field,
                Hstore::make($list)
            );
    }
}