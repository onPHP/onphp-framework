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

ONPHP_METHOD(FieldTable, __construct)
{
	zval *field;
	
	ONPHP_GET_ARGS("z", &field);
	
	ONPHP_UPDATE_PROPERTY(getThis(), "field", field);
}

ONPHP_GETTER(FieldTable, getField, field);

ONPHP_METHOD(FieldTable, toDialectString)
{
	zval *dialect, *cast, *field, *out, *result;
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	field = ONPHP_READ_PROPERTY(getThis(), "field");
	
	ONPHP_CALL_METHOD_1(dialect, "fieldtostring", &out, field);
	
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

zend_function_entry onphp_funcs_FieldTable[] = {
	ONPHP_ME(FieldTable, __construct, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(FieldTable, getField, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FieldTable, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
