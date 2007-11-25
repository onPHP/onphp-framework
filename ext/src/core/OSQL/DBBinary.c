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

#include "core/DB/Dialect.h"
#include "core/OSQL/DBBinary.h"

ONPHP_METHOD(DBBinary, create)
{
	zval *object, *value;
	
	ONPHP_GET_ARGS("z", &value);
	
	ONPHP_MAKE_OBJECT(DBBinary, object);
	
	ONPHP_CALL_PARENT_1_NORET(object, "__construct", NULL, value);
	
	if (EG(exception)) {
		ZVAL_FREE(object);
		return;
	}
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(DBBinary, toDialectString)
{
	zval *dialect, *value, *quoted;
	smart_str string = {0};
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	ONPHP_CALL_METHOD_0(getThis(), "getvalue", &value);
	
	ONPHP_CALL_METHOD_1(dialect, "quotebinary", &quoted, value);
	
	zval_ptr_dtor(&value);
	
	smart_str_appendc(&string, '\'');
	onphp_append_zval_to_smart_string(&string, quoted);
	zval_ptr_dtor(&quoted);
	smart_str_appendc(&string, '\'');
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_DBBinary[] = {
	ONPHP_ME(DBBinary, create, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(DBBinary, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
