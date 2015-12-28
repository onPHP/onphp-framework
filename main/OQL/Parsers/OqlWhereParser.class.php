<?php

/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
class OqlWhereParser extends OqlParser
{
    /**
     * @deprecated
     * @return OqlWhereParser
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return OqlWhereClause
     **/
    protected function makeOqlObject()
    {
        return new OqlWhereClause();
    }

    protected function handleState()
    {
        if ($this->state == self::INITIAL_STATE) {
            $argument = $this->getLogicExpression();
            if ($argument instanceof OqlQueryExpression) {
                $this->oqlObject->setExpression($argument);
            } else {
                $this->error("expecting 'where' expression");
            }
        }

        return self::FINAL_STATE;
    }
}

