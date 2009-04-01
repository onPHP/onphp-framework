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
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	final class OqlTokenType extends StaticFactory
	{
		const NEW_LINE				= 1;
		const PARENTHESES			= 2;
		const PUNCTUATION			= 3;
		const STRING				= 4;
		const NUMBER				= 5;
		const BOOLEAN				= 6;
		const NULL					= 7;
		const PLACEHOLDER			= 8;
		const KEYWORD				= 9;
		const OPERATOR				= 10;
		const AGGREGATE_FUNCTION	= 11;
		const IDENTIFIER			= 12;
	}
?>