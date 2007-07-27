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
#include "ext/standard/php_string.h"

#include "core/Exceptions.h"
#include "core/DB/Dialect.h"
#include "core/OSQL/DBField.h"
#include "core/OSQL/DialectString.h"

ONPHP_METHOD(DBField, create)
{
	zval *field, *table, *object;
	
	if (
		zend_parse_parameters(
			ZEND_NUM_ARGS() TSRMLS_CC,
			"z|z",
			&field,
			&table
		)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	ONPHP_MAKE_OBJECT(DBField, object);
	
	ONPHP_UPDATE_PROPERTY(object, "field", field);
	
	if (ZEND_NUM_ARGS() == 2) {
		zend_call_method_with_1_params(
			&object,
			onphp_ce_DBField,
			NULL,
			"settable",
			NULL,
			table
		);
		
		if (EG(exception)) {
			return;
		}
	}
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(DBField, __construct)
{
	zval *field, *table;
	
	if (
		zend_parse_parameters(
			ZEND_NUM_ARGS() TSRMLS_CC,
			"z|z",
			&field,
			&table
		)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	ONPHP_UPDATE_PROPERTY(getThis(), "field", field);
	
	if (ZEND_NUM_ARGS() == 2) {
		zend_call_method_with_1_params(
			&getThis(),
			onphp_ce_DBField,
			NULL,
			"settable",
			NULL,
			table
		);
		
		if (EG(exception)) {
			return;
		}
	}
}

ONPHP_GETTER(DBField, getField, field);
ONPHP_GETTER(DBField, getTable, table);

ONPHP_METHOD(DBField, toDialectString)
{
	smart_str string = {0};
	zval *table, *field, *dialect, *cast;
	
	if (
		zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &dialect)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	table = ONPHP_READ_PROPERTY(getThis(), "table");
	field = ONPHP_READ_PROPERTY(getThis(), "field");
	cast = ONPHP_READ_PROPERTY(getThis(), "cast");
	
	// either null or instance of DialectString
	if (Z_TYPE_P(table) == IS_OBJECT) {
		zval *tmp;
		
		zend_call_method_with_1_params(
			&table,
			Z_OBJCE_P(table),
			NULL,
			"todialectstring",
			&tmp,
			dialect
		);
		
		if (EG(exception)) {
			return;
		}
		
		onphp_append_zval_to_smart_string(&string, tmp);
		smart_str_appends(&string, ".");
		
		ZVAL_FREE(tmp);
	}
	
	zend_call_method_with_1_params(
		&dialect,
		Z_OBJCE_P(dialect),
		NULL,
		"quotefield",
		&field,
		field
	);
	
	if (EG(exception)) {
		return;
	}
	
	onphp_append_zval_to_smart_string(&string, field);
	
	if (Z_STRLEN_P(cast)) {
		zval *tmp;
		
		ALLOC_INIT_ZVAL(tmp);
		
		ZVAL_STRINGL(tmp, string.c, string.len, 1);
		
		zend_call_method_with_2_params(
			&dialect,
			onphp_ce_Dialect,
			NULL,
			"tocasted",
			&cast,
			tmp,
			cast
		);
		
		ZVAL_FREE(tmp);
		
		if (EG(exception)) {
			return;
		}
		
		RETURN_ZVAL(cast, 1, 0);
	}
	
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(DBField, setTable)
{
	zval *table;
	
	table = ONPHP_READ_PROPERTY(getThis(), "table");
	
	if (Z_TYPE_P(table) != IS_NULL) {
		zend_throw_exception_ex(
			onphp_ce_WrongStateException,
			0 TSRMLS_CC,
			"you should not override setted table"
		);
		return;
	}
	
	if (
		zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &table)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	if (
		(Z_TYPE_P(table) != IS_OBJECT)
		|| (
			!instanceof_function(
				Z_OBJCE_P(table),
				onphp_ce_DialectString TSRMLS_CC
			)
		)
	) {
		zend_class_entry **cep;
		zval *from_table;
		
		// will always succeed
		zend_lookup_class("FromTable", strlen("FromTable"), &cep TSRMLS_CC);
		
		ALLOC_INIT_ZVAL(from_table);
		object_init_ex(from_table, *cep);
		Z_TYPE_P(from_table) = IS_OBJECT;
		
		zend_call_method_with_1_params(
			&from_table,
			Z_OBJCE_P(from_table),
			&Z_OBJCE_P(from_table)->constructor,
			"__construct",
			NULL,
			table
		);
		
		if (EG(exception)) {
			ZVAL_FREE(from_table);
			return;
		}
		
		ONPHP_UPDATE_PROPERTY(getThis(), "table", from_table);
	} else {
		ONPHP_UPDATE_PROPERTY(getThis(), "table", table);
	}
	
	RETURN_ZVAL(getThis(), 1, 0);
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_TWO;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_DBField[] = {
	ONPHP_ME(DBField, create, arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(DBField, __construct, arginfo_two, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBField, getField, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBField, getTable, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBField, setTable, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBField, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
