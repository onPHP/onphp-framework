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
 * @ingroup OSQL
 **/
final class QueryChain extends SQLChain
{
    /**
     * @param $args
     * @param $logic
     * @return QueryChain
     * @throws WrongArgumentException
     */
    public static function block($args, $logic) : QueryChain
    {
        $queryChain = new self;

        foreach ($args as $arg) {
            if (!$arg instanceof SelectQuery) {
                throw new WrongArgumentException(
                    'unsupported object type: ' . get_class($arg)
                );
            }

            $queryChain->exp($arg, $logic);
        }

        return $queryChain;
    }

    /**
     * @param Form $form
     * @throws UnsupportedMethodException
     */
    public function toBoolean(Form $form)
    {
        throw new UnsupportedMethodException('get rid of useless interface');
    }

    /**
     * @param SelectQuery $query
     * @return SQLChain
     */
    public function union(SelectQuery $query)
    {
        return $this->exp($query, CombineQuery::UNION);
    }

    /**
     * @param SelectQuery $query
     * @return SQLChain
     */
    public function unionAll(SelectQuery $query)
    {
        return $this->exp($query, CombineQuery::UNION_ALL);
    }

    /**
     * @param SelectQuery $query
     * @return SQLChain
     */
    public function intersect(SelectQuery $query)
    {
        return $this->exp($query, CombineQuery::INTERSECT);
    }

    /**
     * @param SelectQuery $query
     * @return SQLChain
     */
    public function intersectAll(SelectQuery $query)
    {
        return $this->exp($query, CombineQuery::INTERSECT_ALL);
    }

    /**
     * @param SelectQuery $query
     * @return SQLChain
     */
    public function except(SelectQuery $query)
    {
        return $this->exp($query, CombineQuery::EXCEPT);
    }

    /**
     * @param SelectQuery $query
     * @return SQLChain
     */
    public function exceptAll(SelectQuery $query)
    {
        return $this->exp($query, CombineQuery::EXCEPT_ALL);
    }
}