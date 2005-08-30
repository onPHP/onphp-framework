<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'global.inc.php';

	// system settings
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_erorrs', true);
	
	// include local settings, if any
	require ONPHP_PATH.'local'.EXT_MOD;
?>