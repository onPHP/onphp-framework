<?php
/***************************************************************************
 *   Copyright (C) 2011 by Alexey S. Denisov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Filters
 **/
class CallbackFilter implements Filtrator
{
    /**
     * @var Closure
     */
    private $callback = null;

    /**
     * CallbackFilter constructor.
     * @param Closure $callback
     */
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @deprecated
     * @return CallbackFilter
     **/
    public static function create(Closure $callback)
    {
        return new self($callback);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function apply($value)
    {
        return $this->callback->__invoke($value);
    }
}
