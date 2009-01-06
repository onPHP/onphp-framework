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
	final class OqlWhereClause extends OqlQueryClause
	{
		private $expression = null;
		
		/**
		 * @return OqlWhereClause
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlQueryExpression
		**/
		public function getExpression()
		{
			return $this->expression;
		}
		
		/**
		 * @return OqlWhereClause
		**/
		public function setExpression(OqlQueryExpression $expression)
		{
			Assert::isInstance($expression->getClassName(), 'LogicalObject');
			
			$this->expression = $expression;
			
			return $this;
		}
		
		/**
		 * @return LogicalObject
		**/
		public function toLogic()
		{
			Assert::isNotNull($this->expression);
			
			return $this->expression->evaluate($this->parameters);
		}
	}
?>