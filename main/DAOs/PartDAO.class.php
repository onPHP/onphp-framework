<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton Lebedevich                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	interface PartDAO
	{
		public function dropByParentId($parentId);
		public function dropByBothId($parentId, $childId);

		public function add($parentId, Identifiable $child);
		public function save($parentId, Identifiable $child);
		public function import($parentId, Identifiable $child);
		public function insert($parentId, Identifiable $child);

		public function getById($id);
		public function getListByParentId($parentId);
		public function getChildIdsList($parentId);
	}
?>