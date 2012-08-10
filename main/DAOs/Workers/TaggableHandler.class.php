<?php
/***************************************************************************
 *   Copyright (C) 2009 by Solomatin Alexandr                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	interface TaggableHandler
	{
		// get tag list for object
		public function getCacheObjectTags(IdentifiableObject $object, $className);

		// get tag list for object
		public function getUncacheObjectTags(IdentifiableObject $object, $className);

		// get tag list for query
		public function getQueryTags(SelectQuery $query, $className);

		// get tag list for null object
		public function getNullObjectTags($id, $className);

		// get default tag list
		public function getDefaultTags($className);

	}
?>