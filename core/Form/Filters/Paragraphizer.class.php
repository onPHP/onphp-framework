<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Filters
	**/
	final class Paragraphizer extends BaseFilter implements Instantiatable
	{
		/**
		 * @return Paragraphizer
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function apply($value)
		{
			return preg_replace(
				'~^[^<](.+)\s~Ums',
				'<p>$1</p>'."\n",
				$value
			);
		}
	}
?>