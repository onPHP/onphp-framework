<?php
/***************************************************************************
 *   Copyright (C) 2005 by Sveta Smirnova                                  *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class SHA1Filter extends BaseFilter
	{
		private $raw = false;
		
		public function raw()
		{
			$this->raw = true;
			
			return $this;
		}
		
		public function notRaw()
		{
			$this->raw = false;
			
			return $this;
		}
		
		public function isRaw()
		{
			return $this->raw;
		}
		
		public function apply($value)
		{
			return  sha1($value);
		}
	}
?>
