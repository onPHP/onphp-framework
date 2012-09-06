<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Vladimir A. Altuchov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Helpers
	**/
	interface SingleRange
	{
		public function getStart();
		public function getEnd();
		
		public function contains($probe);
	}
?>