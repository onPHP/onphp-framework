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

	final class AccessMode extends Enumeration
	{
		const READ_ONLY		= 0x00;
		const READ_WRITE	= 0x01;
		
		protected $names	= array(
			0 => 'read only',
			1 => 'read write'
		);
	}
?>