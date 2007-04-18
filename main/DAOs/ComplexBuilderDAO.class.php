<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup DAOs
	**/
	abstract class ComplexBuilderDAO extends StorableDAO
	{
		public function getJoinPrefix($field, $prefix = null)
		{
			return $this->getJoinName($field, $prefix).'__';
		}
		
		public function getJoinName($field, $prefix = null)
		{
			return dechex(crc32($prefix.$this->getTable())).'_'.$field;
		}
	}
?>