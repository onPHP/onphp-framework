<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Garmonbozia Research Group                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Ideal Identifiable interface implementation. ;-)
	 *
	 * @see Identifiable
	 * 
	 * @ingroup Base
	**/
	class /* spirit of */ IdentifiableObject implements Identifiable
	{
		protected $id = null;
		
		public static function wrap($id)
		{
			return self::create()->setId($id);
		}
		
		final public function getId()
		{
			if (
				$this->id instanceof Identifier
				&& $this->id->isFinalized()
			)
				return $this->id->getId();
			else
				return $this->id;
		}
		
		final public function setId($id)
		{
			$this->id = $id;
			
			return $this;
		}
	}
?>