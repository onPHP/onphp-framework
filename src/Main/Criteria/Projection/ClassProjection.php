<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Criteria\Projection;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\Prototyped;
use OnPHP\Core\OSQL\DBField;
use OnPHP\Core\OSQL\JoinCapableQuery;
use OnPHP\Main\Criteria\Criteria;
use OnPHP\Main\Util\ClassUtils;

/**
 * @ingroup Projection
**/
class ClassProjection implements ObjectProjection
{
	protected $className	= null;

	/**
	 * @return ClassProjection
	**/
	public static function create($class)
	{
		return new self($class);
	}

	public function __construct($class)
	{
		Assert::isTrue(
			ClassUtils::isInstanceOf($class, Prototyped::class)
		);

		if (is_object($class))
			$this->className = get_class($class);
		else
			$this->className = $class;
	}

	/**
	 * @return JoinCapableQuery
	**/
	public function process(Criteria $criteria, JoinCapableQuery $query)
	{
		$dao = call_user_func(array($this->className, 'dao'));

		foreach ($dao->getFields() as $field)
			$this->subProcess(
				$query,
				DBField::create($field, $dao->getTable())
			);

		return $query;
	}

	/* void */ protected function subProcess(
		JoinCapableQuery $query, DBField $field
	)
	{
		$query->get($field);
	}
}
?>
