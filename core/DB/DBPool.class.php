<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Pool of DB's instances.
	 * 
	 * @ingroup DB
	**/
	final class DBPool extends Singleton implements Instantiatable
	{
		private $pool = array();
		
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public static function getByDao(GenericDAO $dao)
		{
			return self::me()->getLink($dao->getLinkName());
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
			
			throw new MissingElementException(
				"can't find link with '{$name}' name"
			);
		}
	}
?>