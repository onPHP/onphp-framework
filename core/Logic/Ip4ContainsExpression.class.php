<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
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
class Ip4ContainsExpression implements LogicalObject, MappableObject
{
    /** @var null  */
    private $range = null;
    /** @var null  */
    private $ip = null;

    /**
     * Ip4ContainsExpression constructor.
     * @param $range
     * @param $ip
     */
    public function __construct($range, $ip)
    {
        $this->range = $range;
        $this->ip = $ip;
    }

    /**
     * @param Dialect $dialect
     * @throws UnimplementedFeatureException
     */
    public function toDialectString(Dialect $dialect)
    {
        return $dialect->quoteIpInRange($this->range, $this->ip);
    }

    /**
     * @param ProtoDAO $dao
     * @param JoinCapableQuery $query
     * @return Ip4ContainsExpression
     */
    public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
    {
        return new self(
            $dao->guessAtom($this->range, $query),
            $dao->guessAtom($this->ip, $query)
        );
    }

    /**
     * @param Form $form
     * @throws UnimplementedFeatureException
     */
    public function toBoolean(Form $form)
    {
        throw new UnimplementedFeatureException('Author was too lazy to make it');
    }
}