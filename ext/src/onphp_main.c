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

#include "ext/spl/spl_functions.h"

#include "onphp_main.h"
#include "onphp_util.h"

#include "main/DAOs/Handlers/SegmentHandler.h"

PHP_MINIT_FUNCTION(onphp_main)
{
	REGISTER_ONPHP_INTERFACE(SegmentHandler);
	
	return SUCCESS;
}
