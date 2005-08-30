<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

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
				throw new WrongArgumentException(
					"knows nothing about such id == {$id}"
				);
		}
		
		public static function getList(Enumeration $enum)
		{
			$list = array();
			$class = get_class($enum);
			$names = $enum->getNameList();
			
			foreach ($names as $id => &$val)
				$list[] = new $class($id);

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