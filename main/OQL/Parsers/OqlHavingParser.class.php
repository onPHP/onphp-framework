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
final class OqlHavingParser extends OqlParser
{
    const CLASS_NAME = 'HavingProjection';

    /**
     * @deprecated
     * @return OqlHavingParser
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return OqlHavingClause
     **/
    protected function makeOqlObject()
    {
        return new OqlHavingClause();
    }

    protected function handleState()
    {
        if ($this->state == self::INITIAL_STATE) {
            if ($argument = $this->getLogicExpression()) {
                $this->oqlObject->setExpression(
                    $this->makeQueryExpression(self::CLASS_NAME, $argument)
                );

            } else {
                $this->error("expecting 'having' expression");
            }
        }

        return self::FINAL_STATE;
    }
}

?>