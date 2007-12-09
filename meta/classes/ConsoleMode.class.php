<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * man console_codes
	 * 
	 * @ingroup MetaBase
	**/
	final class ConsoleMode /* extends StaticFactory */
	{
		const ATTR_RESET_ALL		= 0;
		const ATTR_BOLD				= 1;
		const ATTR_HALF_BRIGHT		= 2;
		const ATTR_UNDERSCORE		= 4;
		const ATTR_BLINK			= 5;
		const ATTR_REVERSE_VIDEO	= 7;
		
		// unused attributes: 10, 11, 12, 21, 22, 24, 25, 27
		
		const FG_BLACK				= 30;
		const FG_RED				= 31;
		const FG_GREEN				= 32;
		const FG_BROWN				= 33;
		const FG_BLUE				= 34;
		const FG_MAGENTA			= 35;
		const FG_CYAN				= 36;
		const FG_WHITE				= 37;
		
		// unused foregrounds: 38, 39
		
		const BG_BLACK				= 40;
		const BG_RED				= 41;
		const BG_GREEN				= 42;
		const BG_BROWN				= 43;
		const BG_BLUE				= 44;
		const BG_MAGENTA			= 45;
		const BG_CYAN				= 46;
		const BG_WHITE				= 47;
		
		// unused background: 49
	}
?>