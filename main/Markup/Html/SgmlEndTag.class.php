<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Html
	 * @ingroup Module
	**/
	final class SgmlEndTag extends SgmlTag
	{
		/**
		 * @return SgmlEndTag
		**/
		public static function create()
		{
			return new self;
		}
	}
