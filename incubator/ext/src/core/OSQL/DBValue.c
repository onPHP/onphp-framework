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

#include "core/DB/Dialect.h"
#include "core/OSQL/DBValue.h"

ONPHP_METHOD(DBValue, create)
{
	zval *object, *value;
	
	ONPHP_GET_ARGS("z", &value);
	
	ONPHP_MAKE_OBJECT(DBValue, object);
	
	ONPHP_UPDATE_PROPERTY(object, "value", value);
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(DBValue, __construct)
{
	zval *value;
	
	ONPHP_GET_ARGS("z", &value);
	
	ONPHP_UPDATE_PROPERTY(getThis(), "value", value);
}

ONPHP_GETTER(DBValue, getValue, value);

ONPHP_METHOD(DBValue, toDialectString)
{
	zval *dialect, *cast, *value, *out, *result;
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	value = ONPHP_READ_PROPERTY(getThis(), "value");
	
	ONPHP_CALL_METHOD_1(dialect, "quotevalue", &out, value);
	
	cast = ONPHP_READ_PROPERTY(getThis(), "cast");
	
	if (Z_STRLEN_P(cast)) {
		ONPHP_CALL_METHOD_2(dialect, "tocasted", &result, out, cast);
		zval_ptr_dtor(&out);
		RETURN_ZVAL(result, 1, 1);
	} else {
		RETURN_ZVAL(out, 1, 1);
	}
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
