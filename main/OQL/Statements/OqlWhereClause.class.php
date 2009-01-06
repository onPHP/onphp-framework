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
	final class OqlWhereClause extends OqlQueryListedClause
	{
		/**
		 * @return OqlWhereClause
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return LogicalChain
		**/
		public function toExpression()
		{
			$chain = array();
			foreach ($this->list as $property) {
				$chain[] = $property->evaluate($this->parameters);
			}
			
			return Expression::andBlock($chain);
		}
	}
?>