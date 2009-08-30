<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup DAOs
	**/
	abstract class ValueObjectDAO extends Singleton
	{
		abstract protected function makeSelf(&$array, $prefix = null);
		
		public function makeObject(&$array, $prefix = null)
		{
			return $this->makeSelf($array, $prefix);
		}
		
		public function makeCascade(
			/* Identifiable */ $object,
			&$array,
			$prefix = null
		)
		{
			return $object;
		}
		
		public function makeJoiners(
			/* Identifiable */ $object,
			&$array,
			$prefix = null
		)
		{
			return $object;
		}
	}
?>