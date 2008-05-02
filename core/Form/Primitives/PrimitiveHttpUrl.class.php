<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Ivan Y. Khvostishkov                       *
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
		public function import(array $scope)
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
		
		public function importValue($value)
		{
			if ($value instanceof HttpUrl) {
				
				return
					$this->import(
						array($this->getName() => $value->toString())
					);
			}
			
			return parent::importValue(null);
		}
		
		public function exportValue()
		{
			if (!$this->value)
				return null;
			
			return $this->value->toString();
		}
	}
?>