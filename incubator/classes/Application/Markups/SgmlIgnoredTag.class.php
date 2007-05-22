<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class SgmlIgnoredTag extends SgmlTag
	{
		private $cdata = null;
		
		/**
		 * @return SgmlIgnoredTag
		**/
		public static function create()
		{
			return new self;
		}
		
		public static function comment()
		{
			return self::create()->setId('!--');
		}
		
		/**
		 * @return SgmlIgnoredTag
		**/
		public function setCdata(Cdata $cdata)
		{
			$this->cdata = $cdata;
			
			return $this;
		}
		
		public function getCdata()
		{
			return $this->cdata;
		}
	}
?>