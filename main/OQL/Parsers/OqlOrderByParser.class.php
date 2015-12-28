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
final class OqlOrderByParser extends OqlParser
{
    /**
     * @deprecated
     * @return OqlOrderByParser
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return OqlOrderByClause
     **/
    protected function makeOqlObject()
    {
        return new OqlOrderByClause();
    }

    protected function handleState()
    {
        if ($this->state == self::INITIAL_STATE) {
            $list = $this->getCommaSeparatedList(
                [$this, 'getArgumentExpression'],
                "expecting expression in 'order by'"
            );

            foreach ($list as $argument) {
                $this->oqlObject->add($argument);
            }
        }

        return self::FINAL_STATE;
    }

    /**
     * @return OqlOrderByExpression
     **/
    protected function getArgumentExpression()
    {
        $expression = $this->getLogicExpression();

        $token = $this->tokenizer->peek();
        if ($this->checkKeyword($token, ['asc', 'desc'])) {
            $direction = ($token->getValue() == 'asc');
            $this->tokenizer->next();

        } else {
            $direction = null;
        }

        return new OqlOrderByExpression($expression, $direction);
    }
}

?>