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
	
	abstract class DimensionFilter extends DimensionStep
	{
		// DimensionStep::FILTER_MASK + id
		const SINGLE_MEMBER	= 0x101;
		const LEVEL			= 0x102;
		
		// TODO: use actions: initial, insert, append, prepend, difference.
		
		// implement me.
	}
?>