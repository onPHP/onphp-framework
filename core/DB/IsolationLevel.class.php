<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class IsolationLevel extends Enumeration
	{
		const READ_COMMITTED	= 0x00;
		const READ_UNCOMMITTED	= 0x01;
		const REPEATABLE_READ	= 0x02;
		const SERIALIZABLE		= 0x03;
		
		protected $names	= array(
			0 => 'read commited',
			1 => 'read uncommitted',
			2 => 'repeatable read',
			3 => 'serializable'
		);
	}
?>