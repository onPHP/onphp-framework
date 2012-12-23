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

	/**
	 * @ingroup Html
	 * @ingroup Module
	**/
	abstract class SgmlTag extends SgmlToken
	{
		private $id = null;
		
		/**
		 * @return SgmlTag
		**/
		public function setId($id)
		{
			$this->id = $id;
			
			return $this;
		}
		
		public function getId()
		{
			return $this->id;
		}
	}
