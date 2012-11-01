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

	namespace Onphp;

	final class OqlGroupByParser extends OqlParser
	{
		const CLASS_NAME = '\Onphp\GroupByPropertyProjection';
		
		/**
		 * @return \Onphp\OqlGroupByParser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\OqlProjectionClause
		**/
		protected function makeOqlObject()
		{
			return OqlProjectionClause::create();
		}
		
		protected function handleState()
		{
			if ($this->state == self::INITIAL_STATE) {
				$list = $this->getCommaSeparatedList(
					array($this, 'getLogicExpression'),
					"expecting expression in 'group by' clause"
				);
				
				foreach ($list as $argument) {
					$this->oqlObject->add(
						$this->makeQueryExpression(self::CLASS_NAME, $argument)
					);
				}
			}
			
			return self::FINAL_STATE;
		}
	}
?>