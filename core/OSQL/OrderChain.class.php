<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup OSQL
 **/
final class OrderChain implements DialectString, MappableObject
{
    /** @var array  */
    private $chain = [];

    /**
     * @deprecated
     *
     * @return OrderChain
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @param $order
     * @return OrderChain
     */
    public function prepend($order) : OrderChain
    {
        if ($this->chain) {
            array_unshift($this->chain, $this->makeOrder($order));
        } else {
            $this->chain[] = $this->makeOrder($order);
        }

        return $this;
    }

    /**
     * @param $object
     * @return OrderBy
     */
    private function makeOrder($object) : OrderBy
    {
        if ($object instanceof OrderBy) {
            return $object;
        } elseif ($object instanceof DialectString) {
            return new OrderBy($object);
        }

        return
            new OrderBy(
                new DBField($object)
            );
    }

    /**
     * @return OrderBy
     **/
    public function getLast()
    {
        return end($this->chain);
    }

    /**
     * @return array
     */
    public function getList() : array
    {
        return $this->chain;
    }

    /**
     * @return int
     */
    public function getCount() : int
    {
        return count($this->chain);
    }

    /**
     * @param ProtoDAO $dao
     * @param JoinCapableQuery $query
     * @return OrderChain
     */
    public function toMapped(ProtoDAO $dao, JoinCapableQuery $query) : OrderChain
    {
        $chain = new self;

        foreach ($this->chain as $order) {
            $chain->add($order->toMapped($dao, $query));
        }

        return $chain;
    }

    /**
     * @param $order
     * @return OrderChain
     */
    public function add($order) : OrderChain
    {
        $this->chain[] = $this->makeOrder($order);

        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return null|string
     */
    public function toDialectString(Dialect $dialect)
    {
        if (!$this->chain) {
            return null;
        }

        $out = null;

        foreach ($this->chain as $order) {
            $out .= $order->toDialectString($dialect) . ', ';
        }

        return rtrim($out, ', ');
    }
}

?>