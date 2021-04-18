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

use OnPHP\Core\Base\Assert;
use OnPHP\Core\DB\DBPool;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\OSQL\DBField;
use OnPHP\Core\OSQL\SelectQuery;

/**
 * @ingroup Containers
**/
final class ManyToManyLinkedLazy extends ManyToManyLinkedWorker
{
	/**
	 * @throws WrongArgumentException
	 * @return ManyToManyLinkedLazy
	**/
	public function sync($insert, $update = [], $delete = [])
	{
		Assert::isTrue($update === array());

		$dao = $this->container->getDao();

		$db = DBPool::getByDao($dao);

		if ($insert)
			for ($i = 0, $size = count($insert); $i < $size; ++$i)
				$db->queryNull($this->makeInsertQuery($insert[$i]));

		if ($delete) {
			$db->queryNull($this->makeDeleteQuery($delete));

			$dao->uncacheByIds($delete);
		}

		return $this;
	}

	/**
	 * @return SelectQuery
	**/
	public function makeFetchQuery()
	{
		$uc = $this->container;

		return
			$this->joinHelperTable(
				$this->makeSelectQuery()->
				dropFields()->
				get(
					new DBField(
						$uc->getChildIdField(),
						$uc->getHelperTable()
					)
				)
			);
	}
}
?>