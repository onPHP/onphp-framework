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
		// class map
		const GROUP_BY_PROJECTION	= 1;
		
		private static $classMap = array(
			self::GROUP_BY_PROJECTION	=> 'GroupByPropertyProjection'
		);
		
		/**
		 * @return OqlSelectGroupByParser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlSelectGroupByClause
		**/
		protected function makeOqlObject()
		{
			return OqlSelectGroupByClause::create();
		}
		
		protected function handleState()
		{
			if ($this->state == self::INITIAL_STATE) {
				$list = $this->getCommaSeparatedList(
					0,	// FIXME: remove
					"expecting identifier in 'group by' expression"
				);
				
				foreach ($list as $argument)
					$this->oqlObject->add(
						$this->makeQueryExpression(
							self::$classMap[self::GROUP_BY_PROJECTION],
							$argument
						)
					);
			}
			
			return self::FINAL_STATE;
		}
		
		/**
		 * @throws SyntaxErrorException
		 * @throws WrongArgumentException
		 * @return OqlQueryParameter
		**/
		protected function getArgumentExpression($context, $message)
		{
			return $this->getIdentifierExpression();
		}
	}
?>