<?php
/****************************************************************************
 *   Copyright (C) 2014 by Alexey S. Denisov                                *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	class CaseWhenExpression implements MappableObject
	{
		private $cases = array();
		private $else = null;

		public static function create($expression = null, $result = null, $else = null)
		{
			return new static($expression, $result, $else);
		}

		public function __construct($expression = null, $result = null, $else = null)
		{
			if ($expression !== null && $result !== null) {
				$this->addCase($expression, $result);
			}
			if ($else !== null) {
				$this->addElse($else);
			}
		}

		/**
		 * @param $expression
		 * @param $result
		 * @return CaseWhenExpression
		 */
		public function addCase($expression, $result)
		{
			$this->cases[] = array($expression, $result);
			return $this;
		}

		/**
		 * @param $result
		 * @return CaseWhenExpression
		 */
		public function addElse($result)
		{
			$this->else = $result;
			return $this;
		}

		/**
		 * @param ProtoDAO $dao
		 * @param JoinCapableQuery $query
		 * @return MappableObject
		 */
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			$expr = new CaseWhenExpression();
			foreach ($this->cases as $case) {
				$expr->addCase(
					$dao->guessAtom($case[0], $query),
					$dao->guessAtom($case[1], $query)
				);
			}
			if ($this->else) {
				$expr->addElse($dao->guessAtom($this->else, $query));
			}

			return $expr;
		}

		public function toDialectString(Dialect $dialect)
		{
			$sqlCases = array();
			foreach ($this->cases as $case) {
				$sqlCases[] = 'WHEN '.$dialect->toFieldString($case[0])
					.' THEN '.$dialect->toValueString($case[1]);
			}
			if ($this->else) {
				$sqlCases[] = 'ELSE '.$dialect->toValueString($this->else);
			}

			return 'CASE '.implode(' ', $sqlCases).' END';
		}
	}