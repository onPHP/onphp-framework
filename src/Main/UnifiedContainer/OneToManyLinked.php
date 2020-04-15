<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\UnifiedContainer;

use OnPHP\Core\Base\Identifiable;
use OnPHP\Main\DAO\GenericDAO;

/**
 * @ingroup Containers
**/
abstract class OneToManyLinked extends UnifiedContainer
{
	public function __construct(
		Identifiable $parent, GenericDAO $dao, $lazy = true
	)
	{
		parent::__construct($parent, $dao, $lazy);

		$worker =
			$lazy
				? OneToManyLinkedLazy::class
				: OneToManyLinkedFull::class;

		$this->worker = new $worker($this);
	}

	public function getChildIdField()
	{
		return 'id';
	}

	public function isUnlinkable()
	{
		return false;
	}

	public function getHelperTable()
	{
		return $this->dao->getTable();
	}
}
?>