<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	abstract class BaseMarkupLanguage
	{
		// redefine me
		protected $commonName	= null;
		
		protected $versions		= array();
		protected $version		= null;
		
		abstract public function parse($data);
		abstract public function render(MarkupDocument $data);
		
		public function getCommonName()
		{
			return $this->commonName;
		}
		
		/**
		 * @return BaseMarkupLanguage
		**/
		public function setVersion($version)
		{
			if (!isset($this->versions[$version]))
				throw new WrongArgumentException(
					"dont know nothing about version == '{$version}'"
				);
			
			$this->version = $version;
			
			return $this;
		}
		
		public function getVersion()
		{
			return $this->version;
		}
	}
?>