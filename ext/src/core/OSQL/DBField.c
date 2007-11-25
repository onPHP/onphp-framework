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
#include "core/OSQL/DBField.h"
#include "core/OSQL/DialectString.h"

ONPHP_METHOD(DBField, create)
{
	zval *field, *table, *object;
	
	ONPHP_GET_ARGS("z|z", &field, &table);
	
	ONPHP_MAKE_OBJECT(DBField, object);
	
	ONPHP_UPDATE_PROPERTY(object, "field", field);
	
	if (ZEND_NUM_ARGS() == 2) {
		ONPHP_CALL_METHOD_1(object, "settable", NULL, table);
	}
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(DBField, __construct)
{
	zval *field, *table;
	
	ONPHP_GET_ARGS("z|z", &field, &table);
	
	ONPHP_UPDATE_PROPERTY(getThis(), "field", field);
	
	if (ZEND_NUM_ARGS() == 2) {
		ONPHP_CALL_METHOD_1(getThis(), "settable", NULL, table);
	}
}

ONPHP_GETTER(DBField, getField, field);
ONPHP_GETTER(DBField, getTable, table);

ONPHP_METHOD(DBField, toDialectString)
{
	smart_str string = {0};
	zval *table, *field, *dialect, *cast, *quoted;
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	table = ONPHP_READ_PROPERTY(getThis(), "table");
	field = ONPHP_READ_PROPERTY(getThis(), "field");
	cast = ONPHP_READ_PROPERTY(getThis(), "cast");
	
	// either null or instance of DialectString
	if (Z_TYPE_P(table) == IS_OBJECT) {
		zval *tmp;
		
		ONPHP_CALL_METHOD_1(table, "todialectstring", &tmp, dialect);
		
		onphp_append_zval_to_smart_string(&string, tmp);
		zval_ptr_dtor(&tmp);
		smart_str_appendc(&string, '.');
	}
	
	ONPHP_CALL_METHOD_1(dialect, "quotefield", &quoted, field);
	
	onphp_append_zval_to_smart_string(&string, quoted);
	
	zval_ptr_dtor(&quoted);
	
	smart_str_0(&string);
	
	if (Z_STRLEN_P(cast)) {
		zval *tmp, *out;
		
		ALLOC_INIT_ZVAL(tmp);
		
		ZVAL_STRINGL(tmp, string.c, string.len, 1);
		
		ONPHP_CALL_METHOD_2_NORET(dialect, "tocasted", &out, tmp, cast);
		
		ZVAL_FREE(tmp);
		smart_str_free(&string);
		
		if (EG(exception)) {
			return;
		}
		
		RETURN_ZVAL(out, 1, 1);
	}
	
	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(DBField, setTable)
{
	zval *table;
	
	if (Z_TYPE_P(ONPHP_READ_PROPERTY(getThis(), "table")) != IS_NULL) {
		ONPHP_THROW(
			WrongStateException,
			"you should not override setted table"
		);
	}
	
	ONPHP_GET_ARGS("z", &table);
	
	if (!ONPHP_INSTANCEOF(table, DialectString)) {
		zval *from_table;
		
		ONPHP_MAKE_FOREIGN_OBJECT("FromTable", from_table);
		
		ONPHP_CALL_METHOD_1_NORET(from_table, "__construct", NULL, table);
		
		if (EG(exception)) {
			ZVAL_FREE(from_table);
			return;
		}
		
		ONPHP_UPDATE_PROPERTY(getThis(), "table", from_table);
		
		zval_ptr_dtor(&from_table);
	} else {
		ONPHP_UPDATE_PROPERTY(getThis(), "table", table);
	}
	
	RETURN_THIS;
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_TWO;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_DBField[] = {
	ONPHP_ME(DBField, create, arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(DBField, __construct, arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(DBField, getField, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBField, getTable, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBField, setTable, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBField, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
