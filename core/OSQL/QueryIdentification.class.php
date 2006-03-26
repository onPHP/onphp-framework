<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	abstract class QueryIdentification implements Query, Stringable
	{
		public function getId()
		{
			return sha1($this->toString());
		}
		
		final public function setId($id)
		{
			throw new UnsupportedMethodException();
		}
		
		public function toString()
		{
			static $dialect = null;
			
			if ($dialect === null)
				$dialect = new ImaginaryDialect();
			
			return $this->toDialectString($dialect);
		}
	}
?>