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

#include "onphp_core.h"

#include "core/OSQL/Castable.h"

ONPHP_METHOD(Castable, castTo)
{
	zval *cast;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &cast) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(getThis(), "cast", cast);

	RETURN_ZVAL(getThis(), 1, 0);
}

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_Castable[] = {
	ONPHP_ME(Castable, castTo, arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
