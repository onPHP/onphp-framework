<?php
/***************************************************************************
 *   Copyright (C) 2005 by Garmonbozia Research Group                      *
 *   garmonbozia@shadanakar.org                                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class /* spirit of */ IdentifiableObject extends NamedObject implements Storable
	{
		private $changed = false;
		
		public static function create()
		{
			return new IdentifiableObject();
		}
		
		public function isChanged()
		{
			return $this->changed;
		}

		public function setChanged()
		{
			$this->changed = true;
		}

		public function setSaved()
		{
			$this->changed = false;
		}

		public function isNew()
		{
			return ($this->getId() === null);
		}
	}
?>