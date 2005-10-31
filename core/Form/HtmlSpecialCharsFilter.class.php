<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton Lebedevich                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * HTML Special Characters replacer.
	 * 
	 * @see FiltrablePrimitive::getDisplayValue()
	**/
	class HtmlSpecialCharsFilter extends BaseFilter
	{
		public function apply($value)
		{
			return htmlspecialchars($value);
		}
	}

?>