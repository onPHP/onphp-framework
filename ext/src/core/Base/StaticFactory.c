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

#include "onphp.h"

#include "core/Base/StaticFactory.h"

ONPHP_METHOD(StaticFactory, __construct)
{
	// doh
}

zend_function_entry onphp_funcs_StaticFactory[] = {
	ONPHP_ME(StaticFactory, __construct, NULL, ZEND_ACC_FINAL | ZEND_ACC_PRIVATE | ZEND_ACC_CTOR)
	{NULL, NULL, NULL}
};
