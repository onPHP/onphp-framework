<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Pool of DB's instances.
	 * 
	 * @ingroup DB
	**/
	final class DBPool implements Instantiatable
	{
		private $pool = array();
		
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function addLink($name, DB $db)
		{
			if (isset($this->pool[$name]))
				throw new WrongArgumentException(
					"already have '{$name}' link"
				);
			
			$this->pool[$name] = $db;
			
			return $this;
		}
		
		public function getLink($name = null)
		{
			// backwards compatibility
			if (!$name)
				return DBFactory::getDefaultInstance();
			elseif (isset($this->pool[$name]))
				return $this->pool[$name];
			
			throw new WrongArgumentException(
				"can't find link with '{$name}' name"
			);
		}
	}
?>