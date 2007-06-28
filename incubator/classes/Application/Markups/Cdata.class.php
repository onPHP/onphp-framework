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

	class Cdata extends SgmlToken
	{
		private $data	= null;
		
		private $strict	= false;
		
		/**
		 * @return Cdata
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return Cdata
		**/
		public function setData($data)
		{
			$this->data = $data;
			
			return $this;
		}
		
		public function getData()
		{
			if ($this->strict)
				return '<![CDATA['.$this->data.']]>';
			else
				return $this->data;
		}
		
		/**
		 * @return Cdata
		**/
		public function setStrict($isStrict)
		{
			Assert::isBoolean($isStrict);
			
			$this->strict = $isStrict;
			
			return $this;
		}
		
		public function isStrict()
		{
			return $this->strict;
		}
	}
?>