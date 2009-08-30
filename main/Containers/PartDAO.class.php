<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Containers
	**/
	interface PartDAO
	{
		public function dropByParentId($parentId);
		public function dropByBothId($parentId, $childId);

		public function add($parentId, Identifiable $child);
		public function save($parentId, Identifiable $child);
		public function import($parentId, Identifiable $child);
		public function insert($parentId, Identifiable $child);

		public function getById($id, $expires = Cache::EXPIRES_MEDIUM);
		public function getListByParentId(
			$parentId, $expires = Cache::DO_NOT_CACHE
		);
		public function getChildIdsList(
			$parentId, $expires = Cache::DO_NOT_CACHE
		);
	}
?>