<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov, Anton E. Lebedevich     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Criteria
	**/
	final class Property
	{
		private $name = null;
		
		/**
		 * @return Property
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct($name)
		{
			$this->name = $name;
		}
		
		public function getName()
		{
			return $this->name;
		}
	}
?>