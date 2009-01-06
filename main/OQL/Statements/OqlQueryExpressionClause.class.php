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
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	abstract class OqlQueryExpressionClause extends OqlQueryClause
	{
		protected $expression = null;
		
		/**
		 * @return OqlQueryExpression
		**/
		public function getExpression()
		{
			return $this->expression;
		}
		
		/**
		 * @return OqlQueryExpressionClause
		**/
		public function setExpression(OqlQueryExpression $expression)
		{
			$this->checkExpression($expression);
			$this->expression = $expression;
			
			return $this;
		}
		
		public function toLogic()
		{
			Assert::isNotNull($this->expression);
			
			return $this->expression->evaluate($this->parameters);
		}
		
		protected static function checkExpression(OqlQueryExpression $expression)
		{
		}
	}
?>