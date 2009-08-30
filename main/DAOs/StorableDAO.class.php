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
	 * @ingroup DAOs
	**/
	abstract class StorableDAO extends GenericDAO
	{
		public function getIdName()
		{
			return 'id';
		}
		
		public function take(Identifiable $object)
		{
			return
				$object->getId()
					? $this->save($object)
					: $this->add($object);
		}
		
		public function add(Identifiable $object)
		{
			return
				$this->inject(
					OSQL::insert(),
					$object->setId(
						DBFactory::getDefaultInstance()->obtainSequence(
							$this->getSequence()
						)
					)
				);
		}
		
		public function save(Identifiable $object)
		{
			return
				$this->inject(
					OSQL::update()->where(
						Expression::eqId($this->getIdName(), $object)
					),
					$object
				);
		}

		protected function inject(
			InsertOrUpdateQuery $query, Identifiable $object
		)
		{
			DBFactory::getDefaultInstance()->queryNull(
				$this->setQueryFields(
					$query->setTable($this->getTable()), $object
				)
			);
			
			$this->uncacheById($object->getId());
			
			// clean out Identifier, if any
			return $object->setId($object->getId());
		}
	}
?>