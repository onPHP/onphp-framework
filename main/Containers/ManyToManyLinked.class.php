<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Containers
	**/
	abstract class ManyToManyLinked extends UnifiedContainer
	{
		abstract public function getHelperTable();

		public function __construct(
			DAOConnected $parent, GenericDAO $dao, $lazy = true
		)
		{
			parent::__construct($parent, $dao, $lazy);
			
			$worker =
				$lazy
					? 'ManyToManyLinkedLazy'
					: 'ManyToManyLinkedFull';
			
			$this->worker = new $worker($this);
		}

		public function getParentTableIdField()
		{
			return 'id';
		}
	}
?>