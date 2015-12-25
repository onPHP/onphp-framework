<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Sergey S. Sergeev                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * The results of queries can be combined using the set
 * operations union, intersection, and difference.
 *
 * query1 UNION [ALL] query2 ....
 * query1 INTERSECT [ALL] query2 ....
 * query1 EXCEPT [ALL] query2 ....
 *
 * @see http://www.postgresql.org/docs/current/interactive/queries-union.html
 *
 * @ingroup OSQL
 **/
final class CombineQuery extends StaticFactory
{
    const
        UNION = 'UNION',
        UNION_ALL = 'UNION ALL',

        INTERSECT = 'INTERSECT',
        INTERSECT_ALL = 'INTERSECT ALL',

        EXCEPT = 'EXCEPT',
        EXCEPT_ALL = 'EXCEPT ALL';

    /**
     * @param $left
     * @param $right
     * @return QueryCombination
     */
    public static function union($left, $right)
    {
        return new QueryCombination($left, $right, self::UNION);
    }

    /**
     * @param array ...$args
     * @return QueryChain
     * @throws WrongArgumentException
     */
    public static function unionBlock(...$args)
    {
        return QueryChain::block($args, self::UNION);
    }

    /**
     * @param $left
     * @param $right
     * @return QueryCombination
     */
    public static function unionAll($left, $right)
    {
        return new QueryCombination($left, $right, self::UNION_ALL);
    }


    /**
     * @param array ...$args
     * @return QueryChain
     * @throws WrongArgumentException
     */
    public static function unionAllBlock(...$args)
    {
        return QueryChain::block($args, self::UNION_ALL);
    }

    /**
     * @param $left
     * @param $right
     * @return QueryCombination
     */
    public static function intersect($left, $right)
    {
        return new QueryCombination($left, $right, self::INTERSECT);
    }

    /**
     * @param array ...$args
     * @return QueryChain
     * @throws WrongArgumentException
     */
    public static function intersectBlock(...$args)
    {
        return QueryChain::block($args, self::INTERSECT);
    }

    /**
     * @param $left
     * @param $right
     * @return QueryCombination
     */
    public static function intersectAll($left, $right)
    {
        return new QueryCombination($left, $right, self::INTERSECT_ALL);
    }

    /**
     * @param array ...$args
     * @return QueryChain
     * @throws WrongArgumentException
     */
    public static function intersectAllBlock(...$args)
    {
        return QueryChain::block($args, self::INTERSECT_ALL);
    }

    /**
     * @param $left
     * @param $right
     * @return QueryCombination
     */
    public static function except($left, $right)
    {
        return new QueryCombination($left, $right, self::EXCEPT);
    }

    /**
     * @param array ...$args
     * @return QueryChain
     * @throws WrongArgumentException
     */
    public static function exceptBlock(...$args)
    {
        return QueryChain::block($args, self::EXCEPT);
    }

    /**
     * @param $left
     * @param $right
     * @return QueryCombination
     */
    public static function exceptAll($left, $right)
    {
        return new QueryCombination($left, $right, self::EXCEPT_ALL);
    }

    /**
     * @param array ...$args
     * @return QueryChain
     * @throws WrongArgumentException
     */
    public static function exceptAllBlock(...$args)
    {
        return QueryChain::block($args, self::EXCEPT_ALL);
    }
}

?>