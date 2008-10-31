<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Sergey S. Sergeev                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class RouterUrlHelper extends StaticFactory
	{
		/**
	     * @return string
	    **/
	    public static function url(array $urlOptions = array(), $name, $reset = false, $encode = true)
	    {
	        return
	        	RouterRewrite::me()->
	        		assemble($urlOptions, $name, $reset, $encode);
	    }
	}
?>