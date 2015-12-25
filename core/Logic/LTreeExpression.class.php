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
 * Extensive facilities for searching through label trees are provided.
 *
 * @see http://www.postgresql.org/docs/current/interactive/ltree.html
 * @ingroup Logic
 **/
final class LTreeExpression extends StaticFactory
{
    const
        ANCESTOR = '@>',
        DESCENDANT = '<@',
        MATCH = '~',
        SEARCH = '@';

    /**
     * Is left argument an ancestor of right (or equal)?
     *
     * @param $left
     * @param $right
     * @return BinaryExpression
     */
    public static function ancestor($left, $right)
    {
        return new BinaryExpression($left, $right, self::ANCESTOR);
    }

    /**
     * Is left argument a descendant of right (or equal)?
     *
     * @param $left
     * @param $right
     * @return BinaryExpression
     */
    public static function descendant($left, $right)
    {
        return new BinaryExpression($left, $right, self::DESCENDANT);
    }

    /**
     * @param $ltree
     * @param $lquery
     * @return BinaryExpression
     */
    public static function match($ltree, $lquery)
    {
        return new BinaryExpression($ltree, $lquery, self::MATCH);
    }

    /**
     * @param $ltree
     * @param $ltxtquery
     * @return BinaryExpression
     */
    public static function search($ltree, $ltxtquery)
    {
        return new BinaryExpression($ltree, $ltxtquery, self::SEARCH);
    }
}