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

	final class OqlSelectHavingParser extends OqlParser
	{
		// class map
		const HAVING_PROJECTION		= 1;
		
		private static $classMap = array(
			self::HAVING_PROJECTION		=> 'HavingProjection'
		);
		
		/**
		 * @return OqlSelectHavingParser
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
				if ($argument = $this->getLogicExpression())
					$this->oqlObject->add(
						$this->makeQueryExpression(
							self::$classMap[self::HAVING_PROJECTION],
							$argument
						)
					);
				else
					$this->error("expecting 'having' expression");
			}
			
			return self::FINAL_STATE;
		}
	}
?>