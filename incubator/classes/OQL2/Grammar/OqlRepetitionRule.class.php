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
	class OqlRepetitionRule extends OqlDecoratedRule
	{
		/**
		 * @return OqlRepetitionRule
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlRepetitionRuleParseStrategy
		**/
		public function getParseStrategy()
		{
			return OqlRepetitionRuleParseStrategy::me();
		}
	}
?>