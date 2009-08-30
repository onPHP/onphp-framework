<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Fully implemented DAO for ObjectTree child.
	 * 
	 * @ingroup DAOs
	**/
	abstract class FinalObjectsTreeDAO extends ObjectsTreeDAO
	{
		final public function getListByParentId($id = null)
		{
			return
				$this->getListByLogic(
					$id === null
						? Expression::isNull('parent_id')
						: Expression::eq('parent_id', $id)
				);
		}
		
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