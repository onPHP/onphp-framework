<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class FinalObjectsTreeDAO extends ObjectsTreeDAO
	{
		final public function import(ObjectsTree $tree)
		{
			return parent::importTree($tree);
		}
		
		final public function save(ObjectsTree $tree)
		{
			return parent::saveTree($tree);
		}
		
		final public function add(ObjectsTree $tree)
		{
			return parent::addTree($tree);
		}
		
		final public function makeObject(&$array, $prefix = null)
		{
			$class = $this->getObjectName();
			
			return parent::makeTreeObject($array, new $class, $prefix);
		}
		
		final public function setQueryFields(
			InsertOrUpdateQuery $query, ObjectsTree $tree
		)
		{
			return parent::setTreeQueryFields($query, $tree);
		}
	}
?>