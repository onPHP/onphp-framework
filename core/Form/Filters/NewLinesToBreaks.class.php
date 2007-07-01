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
	 * Inserts HTML line breaks before all newlines in a string.
	 * 
	 * @ingroup Filters
	**/
	final class NewLinesToBreaks extends BaseFilter
	{
		public function apply($value)
		{
			return nl2br($value);
		}
	}
?>