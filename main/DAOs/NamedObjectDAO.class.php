<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Simple DAO for simple NamedObject.
	 * 
	 * @ingroup DAOs
	**/
	abstract class NamedObjectDAO extends MappedStorableDAO
	{
		// if you will override it later - append this fields to your array
		protected $mapping = array(
			'id'	=> null,
			'name'	=> null
		);
		
		/*
			do not forget to declare in every child:
			
			protected function setQueryFields(
				InsertOrUpdateQuery $query, NamedObject $no
			)
		*/
		
		final public function getByName($name)
		{
			return
				$this->getByLogic(
					Expression::eq(
						new DBField(
							$this->mapping['name']
								? $this->mapping['name']
								: 'name',
							$this->getTable()
						),
						new DBValue($name)
					)
				);
		}
		
		final protected function importNamed(Named $no)
		{
			return
				$this->inject(
					OSQL::insert()->into($this->getTable()), $no
				);
		}
		
		final protected function setNamedQueryFields(
			InsertOrUpdateQuery $query, Named $no
		)
		{
			$query->set('name', $no->getName());
			
			if ($query instanceof InsertQuery)
				return $query->set('id', $no->getId());
			else
				return $query;
		}

		final protected function makeNamedObject(
			&$array, Named $no, $prefix = null
		)
		{
			return
				$no->
					setId($array[$prefix.'id'])->
					setName($array[$prefix.'name']);
		}
	}
?>