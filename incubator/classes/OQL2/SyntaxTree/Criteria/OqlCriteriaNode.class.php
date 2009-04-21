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

	/**
	 * @ingroup OQL
	**/
	final class OqlCriteriaNode extends OqlDialectStringNode
	{
		protected $class = 'Criteria';
		
		/**
		 * @return OqlCriteriaNode
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return Criteria
		**/
		public function toCriteria()
		{
			return $this->toValue();
		}
	}
?>