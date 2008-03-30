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

#include "ext/standard/php_string.h"

#include "core/Exceptions.h"

#include "core/DB/Dialect.h"

#include "core/Logic/LogicalObject.h"

#include "core/OSQL/Query.h"
#include "core/OSQL/DialectString.h"

ONPHP_METHOD(FromTable, __construct)
{
	char *alias;
	unsigned int length = 0;
	zval *table;
	
	ONPHP_GET_ARGS("z|s", &table, &alias, &length);
	
	if (
		(ZEND_NUM_ARGS() == 2)
		&& (Z_TYPE_P(table) == IS_OBJECT)
	) {
		zend_class_entry **sq, **sf;
		
		ONPHP_FIND_FOREIGN_CLASS("SelectQuery", sq);
		ONPHP_FIND_FOREIGN_CLASS("SQLFunction", sf);
		
		if (
			ONPHP_INSTANCEOF(table, LogicalObject)
			|| instanceof_function(Z_OBJCE_P(table), *sq TSRMLS_CC)
			|| instanceof_function(Z_OBJCE_P(table), *sf TSRMLS_CC)
		) {
			ONPHP_THROW(
				WrongArgumentException,
				"you should specify alias, when using \
				SelectQuery or LogicalObject as table"
			);
		}
	}
	
	if (length) {
		ONPHP_UPDATE_PROPERTY_STRINGL(getThis(), "alias", alias, length);
	}
	
	if (
		(Z_TYPE_P(table) == IS_STRING)
		&& strchr(Z_STRVAL_P(table), '.')
	) {
		zval *dot, *array, *schema, *newTable;
		
		ALLOC_INIT_ZVAL(dot);
		ZVAL_STRING(dot, ".", 1);
		
		ALLOC_INIT_ZVAL(array);
		array_init(array);
		
		php_explode(dot, table, array, 2);
		
		// not checking for exceptions, since it can not fail
		ONPHP_ARRAY_GET(array, 0, schema);
		ONPHP_ARRAY_GET(array, 1, newTable);
		
		ZVAL_FREE(dot);
		
		ONPHP_UPDATE_PROPERTY(getThis(), "schema", schema);
		ONPHP_UPDATE_PROPERTY(getThis(), "table", newTable);
		
		ZVAL_FREE(array);
	} else {
		ONPHP_UPDATE_PROPERTY(getThis(), "table", table);
	}
}

ONPHP_GETTER(FromTable, getAlias, alias);

ONPHP_METHOD(FromTable, getTable)
{
	zval *alias = ONPHP_READ_PROPERTY(getThis(), "alias");
	
	if (Z_TYPE_P(alias) == IS_NULL) {
		alias = ONPHP_READ_PROPERTY(getThis(), "table");
	}
	
	RETURN_ZVAL(alias, 1, 0);
}

ONPHP_METHOD(FromTable, toDialectString)
{
	zval
		*dialect,
		*result,
		*table = ONPHP_READ_PROPERTY(getThis(), "table"),
		*alias = ONPHP_READ_PROPERTY(getThis(), "alias");
	
	zend_bool is_query = ONPHP_INSTANCEOF(table, Query);
	
	smart_str string = {0};
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	if (ONPHP_INSTANCEOF(table, DialectString)) {
		if (is_query) {
			smart_str_appendc(&string, '(');
		}
		
		ONPHP_CALL_METHOD_1(table, "todialectstring", &result, dialect);
		
		onphp_append_zval_to_smart_string(&string, result);
		
		zval_ptr_dtor(&result);
		
		if (is_query) {
			smart_str_appendc(&string, ')');
		}
		
		smart_str_appendl(&string, " AS ", 4);
		
		ONPHP_CALL_METHOD_1(dialect, "quotetable", &result, alias);
		
		onphp_append_zval_to_smart_string(&string, result);
		
		zval_ptr_dtor(&result);
	} else {
		zval *schema = ONPHP_READ_PROPERTY(getThis(), "schema");
		
		if (Z_TYPE_P(schema) != IS_NULL) {
			ONPHP_CALL_METHOD_1(dialect, "quotetable", &result, schema);
			
			onphp_append_zval_to_smart_string(&string, result);
			
			zval_ptr_dtor(&result);
			
			smart_str_appendc(&string, '.');
		}
		
		ONPHP_CALL_METHOD_1(dialect, "quotetable", &result, table);
		
		onphp_append_zval_to_smart_string(&string, result);
		
		zval_ptr_dtor(&result);
		
		if (Z_TYPE_P(alias) != IS_NULL) {
			smart_str_appendl(&string, " AS ", 4);
			
			ONPHP_CALL_METHOD_1(dialect, "quotetable", &result, alias);
			
			onphp_append_zval_to_smart_string(&string, result);
			
			zval_ptr_dtor(&result);
		}
	}
	
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

static ONPHP_ARGINFO_TWO;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_FromTable[] = {
	ONPHP_ME(FromTable, __construct, arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(FromTable, getAlias, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FromTable, getTable, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FromTable, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
