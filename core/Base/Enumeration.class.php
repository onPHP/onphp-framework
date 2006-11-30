<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Parent of all enumeration classes.
	 * 
	 * @see AccessMode for example
	 * 
	 * @ingroup Base
	 * @ingroup Module
	**/
	abstract class Enumeration extends NamedObject
	{
		protected $names = array(/* override me */);
		
		public function __construct($id)
		{
			$names = $this->getNameList();

			if (isset($names[$id])) {
				$this->id = $id;
				$this->name = $names[$id];
			} else
				throw new MissingElementException(
					"knows nothing about such id == {$id}"
				);
		}
		
		// @{ prevent's serialization of names' array
		public function __sleep()
		{
			return array('id');
		}
		
		public function __wakeup()
		{
			$this->name = $this->names[$this->id];
		}
		/// @}
		
		public static function getList(Enumeration $enum)
		{
			return $enum->getObjectList();
		}
		
		/**
		 * must return any existent ID
		 * 1 should be ok for most enumerations
		**/
		public static function getAnyId()
		{
			return 1;
		}
		
		public function getObjectList()
		{
			$list = array();
			$names = $this->getNameList();
			
			foreach (array_keys($names) as $id)
				$list[] = new $this($id);

			return $list;
		}

		public function toString()
		{
			return $this->name;
		}
		
		public function getNameList()
		{
			return $this->names;
		}
	}
?>