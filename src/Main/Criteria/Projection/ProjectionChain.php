<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Criteria\Projection;

use OnPHP\Core\Base\Assert;
use OnPHP\Main\Criteria\Criteria;
use OnPHP\Core\OSQL\JoinCapableQuery;

/**
 * @ingroup Projection
**/
final class ProjectionChain implements ObjectProjection
{
	private $list = array();

	public function getList()
	{
		return $this->list;
	}

	/**
	 * @return ProjectionChain
	**/
	public function add(ObjectProjection $projection, $name = null)
	{
		if ($name) {
			Assert::isFalse(isset($this->list[$name]));

			$this->list[$name] = $projection;
		} else {
			$this->list[] = $projection;
		}

		return $this;
	}

	/**
	 * @return JoinCapableQuery
	**/
	public function process(Criteria $criteria, JoinCapableQuery $query)
	{
		foreach ($this->list as $projection)
			$projection->process($criteria, $query);

		return $query;
	}

	public function isEmpty()
	{
		return count($this->list) == 0;
	}

	/**
	 * @return ProjectionChain
	**/
	public function dropByType(/* array */ $dropTypes)
	{
		$newList = array();

		if (!is_array($dropTypes))
			$dropTypes = array($dropTypes);

		foreach ($this->list as $name => &$projection) {
			$class = get_class($projection);

			if (!in_array($class, $dropTypes))
				$newList[$name] = $projection;
		}

		// swap
		$this->list = $newList;

		return $this;
	}
}
?>