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

	/**
	 * @ingroup Primitives
	**/
	final class PrimitivePlainList extends PrimitiveList
	{
		/**
		 * @return PrimitivePlainList
		**/
		public function setList($list)
		{
			$this->list = ArrayUtils::getMirrorValues($list);
			
			return $this;
		}
	}
?>