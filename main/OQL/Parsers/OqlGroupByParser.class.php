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

	final class OqlGroupByParser extends OqlParser
	{
		const CLASS_NAME = 'GroupByPropertyProjection';
		
		/**
		 * @return OqlGroupByParser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlProjectionClause
		**/
		protected function makeOqlObject()
		{
			return OqlProjectionClause::create();
		}
		
		/**
		 * @return OqlGroupByParser
		**/
		protected function doParse()
		{
			$list = $this->getCommaSeparatedList(
				array($this, 'getIdentifierExpression'),
				"expecting identifier in 'group by' expression"
			);
			
			foreach ($list as $argument) {
				$this->oqlObject->add(
					$this->makeQueryExpression(self::CLASS_NAME, $argument)
				);
			}
			
			return $this;
		}
	}
?>