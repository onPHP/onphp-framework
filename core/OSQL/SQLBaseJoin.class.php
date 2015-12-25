<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Anton E. Lebedevich                        *
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
abstract class SQLBaseJoin implements SQLTableName, Aliased
{
    /** @var null  */
    protected $subject = null;
    /** @var null  */
    protected $alias = null;
    /** @var LogicalObject|null  */
    protected $logic = null;

    /**
     * SQLBaseJoin constructor.
     * @param $subject
     * @param LogicalObject $logic
     * @param $alias
     */
    public function __construct($subject, LogicalObject $logic, $alias)
    {
        $this->subject = $subject;
        $this->alias = $alias;
        $this->logic = $logic;
    }

    /**
     * @return null
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return null
     */
    public function getTable()
    {
        return $this->alias ? $this->alias : $this->subject;
    }

    /**
     * @param Dialect $dialect
     * @param null $logic
     * @return string
     */
    protected function baseToString(Dialect $dialect, $logic = null) : string
    {
        return
            $logic . 'JOIN '
            . ($this->subject instanceof DialectString
                ?
                $this->subject instanceof Query
                    ? '(' . $this->subject->toDialectString($dialect) . ')'
                    : $this->subject->toDialectString($dialect)
                : $dialect->quoteTable($this->subject)
            )
            . ($this->alias ? ' AS ' . $dialect->quoteTable($this->alias) : null)
            . ' ON ' . $this->logic->toDialectString($dialect);
    }
}
