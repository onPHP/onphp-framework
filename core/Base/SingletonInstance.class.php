<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @deprecated by Occam's Razor
	 * @ingroup Base
	**/
	final class SingletonInstance extends Singleton
	{
		public function __call($class, $args = null)
		{
			return call_user_func_array(
				array('Singleton', 'getInstance'),
				$args
			);
		}
	}
?>