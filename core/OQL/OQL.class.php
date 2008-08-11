<?php
/***************************************************************************
 *   Copyright (C) 2008 by Vladlen Y. Koshelev                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	final class OQL extends StaticFactory
	{
		/**
		 * @return OQLQuery
		**/
		public static function select($query)
		{
			return OqlSelectParser::create()->parse($query);
		}
	}
?>