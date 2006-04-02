<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup DAOs
	**/
	abstract class MappedStorableDAO extends StorableDAO implements MappedDAO
	{
		// override later
		protected $mapping = array();
		
		public function getMapping()
		{
			return $this->mapping;
		}
		
		public function getFields()
		{
			static $fields = null;
			
			if ($fields === null)
				foreach ($this->mapping as $prop => $field)
					$fields[] = ($field === null ? $prop : $field);
			
			return $fields;
		}
	}
?>