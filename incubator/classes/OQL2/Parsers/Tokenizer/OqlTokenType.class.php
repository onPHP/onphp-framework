<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OQL
	**/
	final class OqlTokenType extends StaticFactory
	{
		const PARENTHESES			= 1;
		const PUNCTUATION			= 2;
		const STRING				= 3;
		const NUMBER				= 4;
		const BOOLEAN				= 5;
		const NULL					= 6;
		const PLACEHOLDER			= 7;
		const KEYWORD				= 8;
		const OPERATOR				= 9;
		const AGGREGATE_FUNCTION	= 10;
		const IDENTIFIER			= 11;
		const UNKNOWN				= 12;
		const WHITESPACE			= 13;
	}
?>