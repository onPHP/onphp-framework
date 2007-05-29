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

	abstract class SgmlTag extends SgmlType
	{
		private $id = null;
		
		/**
		 * @return SgmlTag
		**/
		public function setId($id)
		{
			$this->id = strtolower($id);
			
			return $this;
		}
		
		public function getId()
		{
			return $this->id;
		}
	}
?>