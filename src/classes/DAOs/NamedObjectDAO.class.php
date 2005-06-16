<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class NamedObjectDAO extends CommonDAO
	{
		// if you will override it later - append this fields to your array
		protected $fields = array('id', 'name');
		
		public function makeSelectHead()
		{
			return
				OSQL::select()->
				from($this->getTable())->
				arrayGet($this->fields);
		}

		final public function getByName($name)
		{
			$key = $this->getNameCacheKey($name);
			
			if ($no = $this->getCachedByKey($this->getNameCacheKey($name)))
				return $no;
			else {
				$no =
					$this->getByLogic(
						Expression::eq('name', new DBValue($name)),
						Memcached::DO_NOT_CACHE
					);

				return $this->cacheByKey($key, $no, Memcached::EXPIRES_MEDIUM);
			}
		}
		
		final public function uncacheByName($name)
		{
			return
				$this->uncacheByKey($this->getNameCacheKey($name));
		}
		
		final protected function saveNamed(NamedObject $no)
		{
			return
				parent::injectNamed(
					OSQL::update($this->getTable())->
						where(Expression::eq('id', $no->getId())),
					$no
				);
		}

		final protected function addNamed(NamedObject $no)
		{
			return
				self::importNamed(
					$no->setId(
						DBFactory::getDefaultInstance()->
						obtainSequence($this->getSequence())
					)
				);
		}

		final protected function importNamed(NamedObject $no)
		{
			return
				self::injectNamed(
					OSQL::insert()->into($this->getTable()), $no
				);
		}

		final protected function injectNamed(InsertOrUpdateQuery $query, NamedObject $no)
		{
			DBFactory::getDefaultInstance()->queryNull(
				$this->setQueryFields($query, $no)
			);
			
			$this->uncacheList();
			$this->uncacheById($no);
			
			return $no;
		}

		final protected function setNamedQueryFields(InsertOrUpdateQuery $query, NamedObject $no)
		{
			$query->set('name', $no->getName());
			
			if ($query instanceof InsertQuery)
				return $query->
					set('id', $no->getId());
			else
				return $query;
		}

		final protected function makeNamedObject(&$array, NamedObject $no, $prefix = null)
		{
			return $no->setId($array[$prefix.'id'])->setName($array[$prefix.'name']);
		}
		
		private function getNameCacheKey($name)
		{
			return $this->getObjectName().'_name_'.sha1($name);
		}
	}
?>