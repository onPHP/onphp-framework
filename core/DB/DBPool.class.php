<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
	final class DBPool extends Singleton implements Instantiatable
	{
		private $default = null;
		
		private $pool = array();
		
		/**
		 * @return DBPool
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return DB
		**/
		public static function getByDao(GenericDAO $dao)
		{
			return self::me()->getLink($dao->getLinkName());
		}
		
		/**
		 * @return DBPool
		**/
		public function setDefault(DB $db)
		{
			$this->default = $db;
			
			return $this;
		}
		
		/**
		 * @return DBPool
		**/
		public function addLink($name, DB $db)
		{
			if (isset($this->pool[$name]))
				throw new WrongArgumentException(
					"already have '{$name}' link"
				);
			
			$this->pool[$name] = $db;
			
			return $this;
		}
		
		/**
		 * @throws MissingElementException
		 * @return DB
		**/
		public function getLink($name = null)
		{
			$link = null;
			
			// single-DB project
			if (!$name) {
				if (!$this->default)
					throw new MissingElementException(
						'i have to default link and requested link name is null'
					);
				
				$link = $this->default;
			} elseif (isset($this->pool[$name]))
				$link = $this->pool[$name];
			
			if ($link) {
				if (!$link->isConnected())
					$link->connect();
				
				return $link;
			}
			
			throw new MissingElementException(
				"can't find link with '{$name}' name"
			);
		}
	}
?>