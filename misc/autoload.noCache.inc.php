<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/* void */ function __autoload($classname)
	{
		if (strpos($classname, "\0") !== false) {
			/* are you sane? */
			return;
		}
		
		try {
			include $classname.EXT_CLASS;
			return /* void */;
		} catch (BaseException $e) {
			return __autoload_failed($classname, $e->getMessage());
		}
	}
?>