<?php
	/***************************************************************************
	*   Copyright (C) 2012 by Artem Naumenko                                  *
	*                                                                         *
	*   This program is free software; you can redistribute it and/or modify  *
	*   it under the terms of the GNU Lesser General Public License as        *
	*   published by the Free Software Foundation; either version 3 of the    *
	*   License, or (at your option) any later version.                       *
	*                                                                         *
	***************************************************************************/

	/**
	 * @param string $key 
	 * 
	 * @return Listable
	 */
	 
	interface ListGenerator
	{
		public function fetchList($key);
	}
