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

	class /* spirit of */ IdentifiableObject implements Identifiable
	{
		private $id = null;
		
		public static function create()
		{
			return new IdentifiableObject();
		}
		
		public static function wrap($id)
		{
			return self::create()->setId($id);
		}
		
		public function getId()
		{
			return $this->id;
		}
		
		public function setId($id)
		{
			$this->id = $id;
			
			return $this;
		}
	}
?>