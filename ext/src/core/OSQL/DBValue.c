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

#include "zend_interfaces.h"
#include "zend_globals.h"

#include "core/DB/Dialect.h"
#include "core/OSQL/DBValue.h"

ONPHP_METHOD(DBValue, create)
{
	zval *object, *value;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	MAKE_STD_ZVAL(object);

	object->value.obj = onphp_empty_object_new(onphp_ce_DBValue TSRMLS_CC);
	Z_TYPE_P(object) = IS_OBJECT;
	
	ONPHP_UPDATE_PROPERTY(object, "value", value);

	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(DBValue, __construct)
{
	zval *value;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(getThis(), "value", value);
}

ONPHP_METHOD(DBValue, getValue)
{
	zval *value = ONPHP_READ_PROPERTY(getThis(), "value");

	RETURN_ZVAL(value, 1, 0);
}

ONPHP_METHOD(DBValue, toDialectString)
{
	zval *dialect, *cast, *value, *out;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &dialect) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	value = ONPHP_READ_PROPERTY(getThis(), "value");
	
	zend_call_method_with_1_params(
		&dialect,
		Z_OBJCE_P(dialect),
		NULL,
		"quotevalue",
		&out,
		value
	);
	
	if (EG(exception)) {
		return;
	}
	
	cast = ONPHP_READ_PROPERTY(getThis(), "cast");
	
	if (Z_STRLEN_P(cast)) {
		zval *casted;
		
		zend_call_method_with_2_params(
			&dialect,
			Z_OBJCE_P(dialect),
			NULL,
			"tocasted",
			&casted,
			out,
			cast
		);
		
		ZVAL_FREE(out);
		
		if (EG(exception)) {
			return;
		}
		
		RETURN_ZVAL(casted, 1, 1);
	} else {
		// nothing
	}
	
	RETURN_ZVAL(out, 1, 1);
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_DBValue[] = {
	ONPHP_ME(DBValue, create, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(DBValue, __construct, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(DBValue, getValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBValue, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
