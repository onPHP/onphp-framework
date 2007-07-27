<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Helpers
	**/
	abstract class AbstractProtoClass extends Singleton
	{
		abstract protected function makePropertyList();
		abstract protected function makeForm();
		
		final public function getPropertyList()
		{
			static $lists = array();
			
			$className = get_class($this);
			
			if (!isset($lists[$className])) {
				$lists[$className] = $this->makePropertyList();
			}
			
			return $lists[$className];
		}
		
		/**
		 * @return LightMetaProperty
		 * @throws MissingElementException
		**/
		public function getPropertyByName($name)
		{
			$list = $this->getPropertyList();
			
			if (isset($list[$name]))
				return $list[$name];
			
			throw new MissingElementException(
				'unknown property requested by name '."'{$name}'"
			);
		}
		
		public function getMapping()
		{
			$mapping = array();
			
			foreach ($this->getPropertyList() as $name => $property) {
				if (
					!$property->getRelationId()
					|| (
						$property->getRelationId()
						== MetaRelation::ONE_TO_ONE
					) || (
						$property->getFetchStrategyId()
						== FetchStrategy::LAZY
					)
				) {
					$mapping[$name] = $property->getColumnName();
				}
			}
			
			return $mapping;
		}
	}
?>