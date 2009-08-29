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

#include "onphp.h"
#include "onphp_util.h"

#include "core/Exceptions.h"

#include "core/DB/Dialect.h"

ONPHP_METHOD(DropTableQuery, __construct)
{
	char *name;
	unsigned int length;
	zend_bool cascade = 0;
	
	ONPHP_GET_ARGS("s|b", &name, &length, &cascade);
	
	ONPHP_UPDATE_PROPERTY_STRINGL(getThis(), "name", name, length);
	
	if (
		(ZEND_NUM_ARGS() == 2)
		&& cascade
	) {
		ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "cascade", 1);
	}
}

ONPHP_METHOD(DropTableQuery, getId)
{
	ONPHP_THROW(UnsupportedMethodException, NULL);
}

ONPHP_METHOD(DropTableQuery, toDialectString)
{
	zval *dialect, *name, *cascade, *out;
	smart_str string = {0};
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	name = ONPHP_READ_PROPERTY(getThis(), "name");
	cascade = ONPHP_READ_PROPERTY(getThis(), "cascade");
	
	smart_str_appendl(&string, "DROP TABLE ", 11);
	
	ONPHP_CALL_METHOD_1(dialect, "quotetable", &out, name);
	
	onphp_append_zval_to_smart_string(&string, out);
	
	zval_ptr_dtor(&out);
	
	ONPHP_CALL_METHOD_1(dialect, "droptablemode", &out, cascade);
	
	onphp_append_zval_to_smart_string(&string, out);
	
	zval_ptr_dtor(&out);
	
	smart_str_appendc(&string, ';');
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
