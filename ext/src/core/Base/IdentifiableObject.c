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

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	MAKE_STD_ZVAL(object);

	object->value.obj = onphp_empty_object_new(onphp_ce_IdentifiableObject TSRMLS_CC);
	Z_TYPE_P(object) = IS_OBJECT;
	
	ONPHP_UPDATE_PROPERTY(object, "id", id);
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(IdentifiableObject, getId)
{
	zval *id;

	id = ONPHP_READ_PROPERTY(getThis(), "id");

	if (
		Z_TYPE_P(id) == IS_OBJECT
		&& instanceof_function(Z_OBJCE_P(id), onphp_ce_Identifier TSRMLS_CC)
	) {
		if (
			zval_is_true(ONPHP_READ_PROPERTY(id, "final"))
		) {
			id = ONPHP_READ_PROPERTY(id, "id");
		}
	}

	RETURN_ZVAL(id, 1, 0);
}

ONPHP_METHOD(IdentifiableObject, setId)
{
	zval *id;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(getThis(), "id", id);

	RETURN_ZVAL(getThis(), 1, 0);
}

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_IdentifiableObject[] = {
	ONPHP_ME(IdentifiableObject, getId,	NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(IdentifiableObject, setId,	arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(IdentifiableObject, wrap,	arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	{NULL, NULL, NULL}
};
