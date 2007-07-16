/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "ext/spl/spl_functions.h"

#include "onphp_main.h"
#include "onphp_util.h"

#include "core/Base/Identifiable.h"

#include "main/DAOs/DAOConnected.h"
#include "main/DAOs/FullTextDAO.h"
#include "main/DAOs/Handlers/SegmentHandler.h"
#include "main/Flow/ViewResolver.h"

PHP_MINIT_FUNCTION(onphp_main)
{
	REGISTER_ONPHP_INTERFACE(SegmentHandler);
	REGISTER_ONPHP_INTERFACE(ViewResolver);
	REGISTER_ONPHP_INTERFACE(FullTextDAO);
	
	REGISTER_ONPHP_INTERFACE(DAOConnected);
	REGISTER_ONPHP_IMPLEMENTS(DAOConnected, Identifiable);
	
	return SUCCESS;
}
