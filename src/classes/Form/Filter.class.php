<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class Filter /* Factory */
	{
		public static function text()
		{
			return Singletone::getInstance()->TextFilter();
		}
	}
	
	abstract class BaseFilter extends Singletone
	{
		abstract public function filter($value);
	}
	
	class TextFilter extends BaseFilter
	{
		public function filter($value)
		{
			return htmlspecialchars(strip_tags(trim($value)));
		}
	}
?>