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
	 * @ingroup Flow
	**/
	class DefaultHandlerController implements HandlerMapping
	{
		public function getController(HttpRequest $request)
		{
			$controller = null;
			
			if (!isset($_GET['area']) || $_GET['area'] == DEFAULT_MODULE) {
				$controller = DEFAULT_MODULE;
			} elseif (
				defined('PATH_MODULES')
				&& is_readable(PATH_MODULES.$_GET['area'].EXT_CLASS)
			) {
				$controller = $_GET['area'];
			}

			return new $controller;
		}
	}
?>