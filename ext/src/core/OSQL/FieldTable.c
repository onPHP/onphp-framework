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
#include "onphp_core.h"

#include "zend_globals.h"
#include "zend_interfaces.h"

#include "core/DB/Dialect.h"
#include "core/OSQL/FieldTable.h"

ONPHP_METHOD(FieldTable, __construct)
{
	zval *field;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &field) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(getThis(), "field", field);
}

ONPHP_METHOD(FieldTable, getField)
{
	zval *field = ONPHP_READ_PROPERTY(getThis(), "field");

	RETURN_ZVAL(field, 1, 0);
}

ONPHP_METHOD(FieldTable, toDialectString)
{
	zval *dialect, *cast, *field, *out;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &dialect) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	field = ONPHP_READ_PROPERTY(getThis(), "field");
	
	zend_call_method_with_1_params(
		&dialect,
		Z_OBJCE_P(dialect),
		NULL,
		"fieldtostring",
		&out,
		field
	);
	
	if (EG(exception)) {
		return;
	}
	
	cast = ONPHP_READ_PROPERTY(getThis(), "cast");
	
	if (Z_STRLEN_P(cast)) {
		zend_call_method_with_2_params(
			&dialect,
			Z_OBJCE_P(dialect),
			NULL,
			"tocasted",
			&out,
			out,
			cast
		);
		
		if (EG(exception)) {
			return;
		}
	} else {
		// nothing
	}
	
	RETURN_ZVAL(out, 1, 1);
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_FieldTable[] = {
	ONPHP_ME(FieldTable, __construct, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(FieldTable, getField, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FieldTable, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
