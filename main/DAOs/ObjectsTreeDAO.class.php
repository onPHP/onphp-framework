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
	 * @see ObjectsTree
	 * @see FinalObjectsTreeDAO
	 * 
	 * @deprecated will be removed during 0.7 session
	 * 
	 * @ingroup DAOs
	**/
	abstract class ObjectsTreeDAO extends GenericDAO
	{
		protected $fields = array(
			'id', 'parent_id', 'name'
		);
		
		final public function setTreeQueryFields(
			InsertOrUpdateQuery $query, ObjectsTree $tree
		)
		{
			$query->
				set('id', $tree->getId())->
				set('name', $tree->getName());
			
			if ($parent = $tree->getParent())
				$query->setId('parent_id', $parent);
			
			return $query;
		}
		
		final public function makeTreeObject(
			&$array, ObjectsTree $tree, $prefix = null
		)
		{
			$tree->
				setId($array[$prefix.'id'])->
				setName($array[$prefix.'name']);
			
			if (isset($array[$prefix.'parent_id']))
				$tree->setParent(
					$this->getById($array[$prefix.'parent_id'])
				);
			
			return $tree;
		}

		final protected function saveTree(ObjectsTree $tree)
		{
			return
				self::injectTree(
					OSQL::update($this->getTable())->
						where(Expression::eqId('id', $tree)),
					$tree
				);
		}
		
		final protected function addTree(ObjectsTree $tree)
		{
			return
				self::importTree(
					$tree->setId(
						DBFactory::getDefaultInstance()->obtainSequence(
							$this->getSequence()
						)
					)
				);
		}
		
		final protected function importTree(ObjectsTree $tree)
		{
			return
				self::injectTree(
					OSQL::insert()->into($this->getTable()),
					$tree
				);
		}
		
		final protected function injectTree(
			InsertOrUpdateQuery $query, ObjectsTree $tree
		)
		{
			DBFactory::getDefaultInstance()->queryNull(
				$this->setQueryFields($query, $tree)
			);
			
			$this->uncacheById($tree->getId());
			
			return $tree;
		}
	}
?>