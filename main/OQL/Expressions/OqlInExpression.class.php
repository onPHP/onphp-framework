<?php
/****************************************************************************
 *   Copyright (C) 2008 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OQL
	**/
	final class OqlInExpression extends OqlQueryExpression
	{
		private $logic = null;
		
		public function __construct(
			OqlQueryParameter $left, OqlQueryParameter $right, $logic
		)
		{
			$this->
				addParameter($left)->
				addParameter($right);
			
			$this->logic = $logic;
		}
		
		/**
		 * @return InExpression
		**/
		public function evaluate($values)
		{
			switch ($this->logic) {
				case InExpression::IN:
					return Expression::in(
						$this->getParameter(0)->evaluate($values),
						$this->getParameter(1)->evaluate($values)
					);
				
				case InExpression::NOT_IN:
					return Expression::notIn(
						$this->getParameter(0)->evaluate($values),
						$this->getParameter(1)->evaluate($values)
					);
				
				default:
					throw new UnsupportedMethodException(
						"'{$this->logic}' doesn't supported yet"
					);
			}
		}
	}
?>