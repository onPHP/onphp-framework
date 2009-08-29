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

#include "core/Base/Identifier.h"

ONPHP_CREATOR(Identifier);

ONPHP_METHOD(Identifier, wrap)
{
	zval *object, *id;
	
	ONPHP_GET_ARGS("z", &id);
	
	ONPHP_MAKE_OBJECT(Identifier, object);
	
	ONPHP_UPDATE_PROPERTY(object, "id", id);
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_GETTER(Identifier, getId, id);
ONPHP_SETTER(Identifier, setId, id);

ONPHP_METHOD(Identifier, finalize)
{
	ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "final", 1);
	
	RETURN_THIS;
}

ONPHP_METHOD(Identifier, isFinalized)
{
	if (zval_is_true(ONPHP_READ_PROPERTY(getThis(), "final"))) {
		RETURN_TRUE;
	}
	
	RETURN_FALSE;
}

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_Identifier[] = {
	ONPHP_ME(Identifier, create,		NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Identifier, wrap,			arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Identifier, getId,			NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Identifier, setId,			arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Identifier, finalize,		NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Identifier, isFinalized,	NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
