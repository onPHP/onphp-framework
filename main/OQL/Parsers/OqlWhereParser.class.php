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

	final class OqlWhereParser extends OqlParser
	{
		/**
		 * @return OqlWhereParser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlWhereClause
		**/
		protected function makeOqlObject()
		{
			return OqlWhereClause::create();
		}
		
		/**
		 * @return OqlWhereParser
		**/
		protected function doParse()
		{
			$argument = $this->getLogicExpression();
			if ($argument instanceof OqlQueryExpression)
				$this->oqlObject->setExpression($argument);
			else
				$this->error("expecting 'where' expression");
			
			return $this;
		}
	}
?>