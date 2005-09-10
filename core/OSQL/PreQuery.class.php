<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Half wrapper, half holder for passing data between modules and DAO's
	 * 
	 * @deprecated since 0.2.1 in favour of ObjectQuery
	**/
	class PreQuery
	{
		// doomed brothers
		private $limit		= null;
		private $offset		= null;

		// CONVENTION: should be set only from appropriate DAO's constant
		private $order		= null;

		// Ternary: true == ascend, false == descend, null == fsck it
		private $direction	= null;

		// example object
		private $object		= null;

		// appropriate CommonDAO object
		private $dao		= null;

		// where's logic chain
		private $chain		= null;

		// sometimes we want to override DAO's getFields()
		private $fields		= array();

		public function __construct(CommonDAO $dao, Identifiable $object)
		{
			if (get_class($object) !== $dao->getObjectName())
				throw new WrongArgumentException(
					'object does not match given dao or vice versa'
				);

			$this->dao = $dao;
			$this->object = $object;

			$this->direction = new Ternary(null);
			$this->chain = new LogicalChain();
		}
		
		public static function create(CommonDAO $dao, Identifiable $object)
		{
			return new PreQuery($dao, $object);
		}
		
		public static function spawn(DAOConnected $object)
		{
			return new PreQuery($object->dao(), $object);
		}

		public function addField($field)
		{
			$this->fields[] = $field;

			return $this;
		}

		public function dropFields()
		{
			$this->fields = array();

			return $this;
		}

		public function fieldsOverrided()
		{
			return ($this->fields === array() ? false : true);
		}

		public function getLimit()
		{
			return $this->limit;
		}

		public function setLimit($limit)
		{
			$this->limit = $limit;

			return $this;
		}

		public function getOffset()
		{
			return $this->offset;
		}

		public function setOffset($offset)
		{
			$this->offset = $offset;

			return $this;
		}

		public function getOrder()
		{
			return $this->order;
		}

		public function setOrder($order)
		{
			$this->order = $order;

			return $this;
		}

		public function getDirection()
		{
			return $this->direction;
		}

		public function asc()
		{
			$this->direction->setTrue();

			return $this;
		}

		public function desc()
		{
			$this->direction->setFalse();

			return $this;
		}

		public function pointQuery(SelectQuery $query)
		{
			if (!$this->direction->isNull()) {
				if ($this->direction->isTrue())
					$query->asc();
				else
					$query->desc();
			}

			return $query;
		}

		public function limitQuery(SelectQuery $query)
		{
			if ($this->limit || $this->offset)
				$query->limit($this->limit, $this->offset);

			return $query;
		}

		public function &getObject()
		{
			return $this->object;
		}

		public function &dao()
		{
			return $this->dao;
		}

		public function &getChain()
		{
			return $this->chain;
		}

		public function toSelectQuery()
		{
			$query = $this->dao->makeSelectHead();

			if ($this->fieldsOverrided())
				$query->
					dropFields()->
					arrayGet($this->fields)->
					// since we handle only identifiable objects
					get(new DBField('id', $this->dao->getTable()));

			if ($this->order) {
				$query->orderBy($this->order, $this->dao->getTable());

				$this->pointQuery($query);
			}

			if ($this->chain->getSize())
				$query->andWhere($this->chain);

			return $this->limitQuery($query);
		}
	}
?>