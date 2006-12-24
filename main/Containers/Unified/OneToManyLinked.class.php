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
	abstract class OneToManyLinked
		extends UnifiedContainer
		implements OneToManyLinkedInfo
	{
		public function __construct(
			Identifiable $parent, GenericDAO $dao, $lazy = true
		)
		{
			parent::__construct($parent, $dao, $lazy);
			
			$worker =
				$lazy
					? 'OneToManyLinkedLazy'
					: 'OneToManyLinkedFull';
			
			$this->worker = new $worker($this);
		}
		
		public static function getChildIdField()
		{
			return 'id';
		}

		public static function isUnlinkable()
		{
			return false;
		}
	}
?>