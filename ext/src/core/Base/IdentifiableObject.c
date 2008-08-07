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
#include "core/Base/IdentifiableObject.h"

ONPHP_METHOD(IdentifiableObject, wrap)
{
	zval *object, *id;
	
	ONPHP_GET_ARGS("z", &id);
	
	ONPHP_MAKE_OBJECT(IdentifiableObject, object);
	
	ONPHP_UPDATE_PROPERTY(object, "id", id);
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(IdentifiableObject, getId)
{
	zval *id;
	
	id = ONPHP_READ_PROPERTY(getThis(), "id");
	
	if (ONPHP_INSTANCEOF(id, Identifier)) {
		if (
			zval_is_true(ONPHP_READ_PROPERTY(id, "final"))
		) {
			id = ONPHP_READ_PROPERTY(id, "id");
		}
	}
	
	RETURN_ZVAL(id, 1, 0);
}

ONPHP_SETTER(IdentifiableObject, setId, id);

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_IdentifiableObject[] = {
	ONPHP_ME(IdentifiableObject, getId,	NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(IdentifiableObject, setId,	arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(IdentifiableObject, wrap,	arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	{NULL, NULL, NULL}
};
