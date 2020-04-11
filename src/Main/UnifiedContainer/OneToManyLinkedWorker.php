<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\UnifiedContainer;

use OnPHP\Core\OSQL\SelectQuery;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\OSQL\DBField;

/**
 * @ingroup Containers
**/
abstract class OneToManyLinkedWorker extends UnifiedContainerWorker
{
	/**
	 * @return SelectQuery
	**/
	protected function targetize(SelectQuery $query)
	{
		return
			$query->andWhere(
				Expression::eqId(
					new DBField(
						$this->container->getParentIdField(),
						$this->container->getDao()->getTable()
					),
					$this->container->getParentObject()
				)
			);
	}
}
?>