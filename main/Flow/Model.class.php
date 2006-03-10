<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	class Model
	{
		private $vars = array();
		
		public static function create()
		{
			return new self;
		}
		
		public function getList()
		{
			return $this->vars;
		}
		
		public function setVar($name, $var)
		{
			$this->vars[$name] = $var;
			
			return $this;
		}
		
		public function getVar($name)
		{
			return $this->vars[$name];
		}
	}
?>