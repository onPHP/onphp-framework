<?php
/***************************************************************************
 *   Copyright (C) 2010 by Evgeny V. Kokovikhin                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 *
	 * @see 
	 * @see http://memcached.org/
	 *
	 * @ingroup Cache
	**/
	abstract class BaseMemcache extends CachePeer
	{
		const DEFAULT_PORT		= 11211;
		const DEFAULT_HOST		= '127.0.0.1';
	}
?>