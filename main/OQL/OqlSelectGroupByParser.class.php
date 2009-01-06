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

	final class OqlSelectGroupByParser extends OqlParser
	{
		const GROUP_BY_CLASS = 'GroupByPropertyProjection';
		
		/**
		 * @return OqlSelectGroupByParser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlSelectProjectionClause
		**/
		protected function makeOqlObject()
		{
			return OqlSelectProjectionClause::create();
		}
		
		protected function handleState()
		{
			if ($this->state == self::INITIAL_STATE) {
				$list = $this->getCommaSeparatedList(
					"expecting identifier in 'group by' expression"
				);
				
				foreach ($list as $argument) {
					$this->oqlObject->add(
						$this->makeQueryExpression(self::GROUP_BY_CLASS, $argument)
					);
				}
			}
			
			return self::FINAL_STATE;
		}
		
		/**
		 * @return OqlQueryParameter
		**/
		protected function getArgumentExpression()
		{
			return $this->getIdentifierExpression();
		}
	}
?>