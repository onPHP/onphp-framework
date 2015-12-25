<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
final class TruncateQuery extends QueryIdentification
{
    /** @var array|null */
    private $targets = [];

    /**
     * TruncateQuery constructor.
     * @param null $whom
     */
    public function __construct($whom = null)
    {
        if ($whom) {
            if (is_array($whom)) {
                $this->targets = $whom;
            } else {
                $this->targets[] = $whom;
            }
        }
    }

    /**
     * @throws UnsupportedMethodException
     */
    public function getId()
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param $table
     * @return TruncateQuery
     */
    public function table($table) : TruncateQuery
    {
        if ($table instanceof SQLTableName) {
            $this->targets[] = $table->getTable();
        } else {
            $this->targets[] = $table;
        }

        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return string
     * @throws WrongArgumentException
     */
    public function toDialectString(Dialect $dialect) : string
    {
        Assert::isTrue(
            ($this->targets !== []),
            'do not know who should i truncate'
        );

        if ($dialect->hasTruncate()) {
            $head = 'TRUNCATE TABLE ';
        } else {
            $head = 'DELETE FROM ';
        }

        if ($dialect->hasMultipleTruncate()) {
            $query = $head . $this->dumpTargets($dialect, null, ',');
        } else {
            $query = $this->dumpTargets($dialect, $head, ';');
        }

        return $query . ';';
    }

    /**
     * @param Dialect $dialect
     * @param null $prepend
     * @param null $append
     * @return string
     * @throws WrongArgumentException
     */
    private function dumpTargets(Dialect $dialect, $prepend = null, $append = null) : string
    {
        if (count($this->targets) == 1) {
            return $prepend . $dialect->quoteTable(reset($this->targets));
        } else {
            $tables = [];

            foreach ($this->targets as $target) {
                if ($target instanceof DialectString) {
                    $table =
                        $dialect->quoteTable(
                            $target->toDialectString($dialect)
                        );
                } else {
                    $table = $dialect->quoteTable($target);
                }

                $tables[] = $prepend . $table;
            }

            return implode($append . ' ', $tables);
        }

        Assert::isUnreachable();
    }
}
