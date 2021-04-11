<?php
/***************************************************************************
 *   Copyright (C) 2005-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\UnifiedContainer;

use OnPHP\Main\Criteria\Criteria;
use OnPHP\Core\OSQL\SQLFunction;
use OnPHP\Core\OSQL\DBField;
use OnPHP\Core\OSQL\DBValue;
use OnPHP\Core\OSQL\SelectQuery;
use OnPHP\Core\DB\DBPool;
use OnPHP\Core\OSQL\OSQL;
use OnPHP\Core\Logic\Expression;

/**
 * @see UnifiedContainer
 * 
 * @ingroup Containers
**/
abstract class UnifiedContainerWorker
{
	protected $criteria		= null;
	protected $container	= null;

	abstract public function makeFetchQuery();
	abstract public function sync($insert, $update = [], $delete = []);

	public function __construct(UnifiedContainer $uc)
	{
		$this->container = $uc;
	}

	/**
	 * @return UnifiedContainerWorker
	**/
	public function setCriteria(Criteria $criteria)
	{
		$this->criteria = $criteria;

		return $this;
	}

	/**
	 * @return Criteria
	**/
	public function getCriteria()
	{
		return $this->criteria;
	}

	/**
	 * @return SelectQuery
	**/
	public function makeCountQuery()
	{
		$query = $this->makeFetchQuery();

		if ($query->isDistinct()) {
			$countFunction =
				SQLFunction::create(
					'count',
					DBField::create(
						$this->container->getDao()->getIdName(),
						$this->container->getDao()->getTable()
					)
				)->
				setAggregateDistinct();

			$query->unDistinct();

		} else {
			$countFunction = SQLFunction::create('count', DBValue::create('*'));
		}

		return $query->
			dropFields()->
			dropOrder()->
			dropLimit()->
			get(
				$countFunction->setAlias('count')
			);
	}

	public function dropList()
	{
		$dao = $this->container->getDao();

		DBPool::getByDao($dao)->queryNull(
			OSQL::delete()->from($this->container->getHelperTable())->
			where(
				Expression::eq(
					$this->container->getParentIdField(),
					$this->container->getParentObject()->getId()
				)
			)
		);

		$dao->uncacheLists();

		return $this;
	}

	/**
	 * @return SelectQuery
	**/
	protected function makeSelectQuery()
	{
		if ($this->criteria)
			return $this->criteria->toSelectQuery();

		return $this->container->getDao()->makeSelectHead();
	}
}
?>