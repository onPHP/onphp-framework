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
final class OqlSelectPropertiesParser extends OqlParser
{
    // class map
    const SUM_PROJECTION = 'sum';
    const AVG_PROJECTION = 'avg';
    const MIN_PROJECTION = 'min';
    const MAX_PROJECTION = 'max';
    const COUNT_PROJECTION = 'count';
    const DISTINCT_COUNT_PROJECTION = 1;
    const PROPERTY_PROJECTION = 2;

    private static $classMap = [
        self::SUM_PROJECTION => 'SumProjection',
        self::AVG_PROJECTION => 'AverageNumberProjection',
        self::MIN_PROJECTION => 'MinimalNumberProjection',
        self::MAX_PROJECTION => 'MaximalNumberProjection',
        self::COUNT_PROJECTION => 'RowCountProjection',
        self::DISTINCT_COUNT_PROJECTION => 'DistinctCountProjection',
        self::PROPERTY_PROJECTION => 'PropertyProjection'
    ];

    /**
     * @deprecated
     * @return OqlSelectPropertiesParser
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return OqlSelectPropertiesClause
     **/
    protected function makeOqlObject()
    {
        return new OqlSelectPropertiesClause();
    }

    protected function handleState()
    {
        if ($this->state == self::INITIAL_STATE) {
            $list = $this->getCommaSeparatedList(
                [$this, 'getArgumentExpression'],
                'expecting expression or aggregate function call'
            );

            foreach ($list as $argument) {
                $this->oqlObject->add($argument);
            }
        }

        return self::FINAL_STATE;
    }

    /**
     * @return OqlQueryParameter
     **/
    protected function getArgumentExpression()
    {
        $token = $this->tokenizer->peek();

        // aggregate function
        if ($this->checkToken($token, OqlToken::AGGREGATE_FUNCTION)) {
            $this->tokenizer->next();

            if ($this->openParentheses(false)) {

                if (($functionName = $this->getTokenValue($token)) == 'count') {
                    if ($this->checkKeyword($this->tokenizer->peek(), 'distinct')) {
                        $this->tokenizer->next();
                        $functionName = self::DISTINCT_COUNT_PROJECTION;
                    }

                    $expression = $this->getLogicExpression();

                } else {
                    $expression = $this->getArithmeticExpression();
                }

                $this->closeParentheses(true, "in function call: {$this->getTokenValue($token)}");

                return $this->makeQueryExpression(
                    self::$classMap[$functionName],
                    $expression,
                    $this->getAlias()
                );

            } else {
                $this->tokenizer->back();
            }
        }

        // property
        if ($this->checkKeyword($token, 'distinct')) {
            $token = $this->tokenizer->next();
            $this->oqlObject->setDistinct(true);
        }

        return $this->makeQueryExpression(
            self::$classMap[self::PROPERTY_PROJECTION],
            $this->getLogicExpression(),
            $this->getAlias()
        );
    }

    /**
     * @return OqlToken
     **/
    private function getAlias()
    {
        if ($this->checkKeyword($this->tokenizer->peek(), 'as')) {
            $this->tokenizer->next();

            if (
                !($alias = $this->tokenizer->next())
                || !$this->checkIdentifier($alias)
            ) {
                $this->error(
                    'expecting alias name:',
                    $this->getTokenValue($alias, true)
                );
            }

            return $alias;
        }

        return null;
    }
}

?>