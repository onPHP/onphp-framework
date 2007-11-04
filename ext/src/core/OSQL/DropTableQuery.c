/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp_core.h"
#include "onphp_util.h"

#include "zend_exceptions.h"

#include "core/Exceptions.h"

#include "core/DB/Dialect.h"
#include "core/OSQL/DropTableQuery.h"

ONPHP_METHOD(DropTableQuery, __construct)
{
	zval *name;
	zend_bool cascade = 0;
	
	ONPHP_GET_ARGS("z|b", &name, &cascade);
	
	ONPHP_UPDATE_PROPERTY(getThis(), "name", name);
	
	if (
		(ZEND_NUM_ARGS() == 2)
		&& cascade
	) {
		ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "cascade", 1);
	}
}

ONPHP_METHOD(DropTableQuery, getId)
{
	zend_throw_exception_ex(
		onphp_ce_UnsupportedMethodException,
		0 TSRMLS_CC,
		NULL
	);
}

ONPHP_METHOD(DropTableQuery, toDialectString)
{
	zval *dialect, *name, *cascade;
	smart_str string = {0};
	
	ONPHP_GET_ARGS("z", &dialect);
	
	name = ONPHP_READ_PROPERTY(getThis(), "name");
	cascade = ONPHP_READ_PROPERTY(getThis(), "cascade");
	
	ONPHP_CALL_METHOD_1(dialect, "quotetable", &name, name);
	ONPHP_CALL_METHOD_1(dialect, "droptablemode", &cascade, cascade);
	
	smart_str_appends(&string, "DROP TABLE ");
	onphp_append_zval_to_smart_string(&string, name);
	onphp_append_zval_to_smart_string(&string, cascade);
	smart_str_appends(&string, ";");
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

static ONPHP_ARGINFO_TWO;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_DropTableQuery[] = {
	ONPHP_ME(DropTableQuery, __construct, arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(DropTableQuery, getId, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(DropTableQuery, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
