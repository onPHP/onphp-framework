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

	/**
	 * Logic like in CacheDaoWorker
	 */
	namespace Onphp;

	class TaggableLayerHandler implements TaggableHandler
	{
		public function getCacheObjectTags(IdentifiableObject $object, $className)
		{
			return array($className);
		}

		public function getUncacheObjectTags(IdentifiableObject $object, $className)
		{
			return array($className);
		}

		public function getQueryTags(SelectQuery $query, $className)
		{
			return array($className);
		}

		public function getNullObjectTags($id, $className)
		{
			return $this->getDefaultTags($className);
		}

		public function getDefaultTags($className)
		{
			return array($className);
		}
	}
?>