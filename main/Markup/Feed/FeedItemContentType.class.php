<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Feed
 **/
final class FeedItemContentType extends Enumeration
{
	const TEXT = 1;
	const HTML = 2;
	const XHTML = 3;

	protected $names = [
		self::TEXT => 'text',
		self::HTML => 'html',
		self::XHTML => 'xhtml'
	];
}

