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

	final class OqlHavingParser extends OqlParser
	{
		const CLASS_NAME = 'HavingProjection';
		
		/**
		 * @return OqlHavingParser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlHavingClause
		**/
		protected function makeOqlObject()
		{
			return OqlHavingClause::create();
		}
		
		/**
		 * @return OqlHavingParser
		**/
		protected function doParse()
		{
			if ($argument = $this->getLogicExpression()) {
				$this->oqlObject->setExpression(
					$this->makeQueryExpression(self::CLASS_NAME, $argument)
				);
			
			} else
				$this->error("expecting 'having' expression");
			
			return $this;
		}
	}
?>