<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveHttpUrl extends PrimitiveString
	{
		public function import($scope)
		{
			if (!$result = parent::import($scope))
				return $result;
			
			try {
				$this->value = HttpUrl::create()->parse($this->value);
			} catch (WrongArgumentException $e) {
				$this->value = null;
				
				return false;
			}
			
			if (!$this->value->isValid()) {
				$this->value = null;
				return false;
			}
			
			$this->value->normalize();
			
			return true;
		}
		
		public function exportValue()
		{
			if (!$this->value)
				return null;
			
			return $this->value->toString();
		}
	}
?>