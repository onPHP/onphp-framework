/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp_core.h"
#include "onphp_util.h"

#include "ext/standard/php_string.h"

#include "core/DB/Dialect.h"
#include "core/OSQL/DBField.h"
#include "core/OSQL/SelectField.h"
#include "core/OSQL/DialectString.h"

ONPHP_METHOD(SelectField, create)
{
	zval *field, *alias, *object;
	
	if (
		zend_parse_parameters(
			ZEND_NUM_ARGS() TSRMLS_CC,
			"zz",
			&field,
			&alias
		)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	ONPHP_MAKE_OBJECT(SelectField, object);
	
	ONPHP_UPDATE_PROPERTY(object, "alias", alias);
	
	zend_call_method_with_1_params(
		&object,
		Z_OBJCE_P(object)->parent,
		&Z_OBJCE_P(object)->parent->constructor,
		"__construct",
		NULL,
		field
	);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(SelectField, __construct)
{
	zval *field, *alias;
	
	if (
		zend_parse_parameters(
			ZEND_NUM_ARGS() TSRMLS_CC,
			"zz",
			&field,
			&alias
		)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	ONPHP_UPDATE_PROPERTY(getThis(), "alias", alias);
	
	zend_call_method_with_1_params(
		&getThis(),
		Z_OBJCE_P(getThis())->parent,
		&Z_OBJCE_P(getThis())->parent->constructor,
		"__construct",
		NULL,
		field
	);
	
	if (EG(exception)) {
		return;
	}
}

ONPHP_GETTER(SelectField, getAlias, alias);

ONPHP_METHOD(SelectField, getName)
{
	zval *field = ONPHP_READ_PROPERTY(getThis(), "field");
	
	if (
		(Z_TYPE_P(field) == IS_OBJECT)
		&& instanceof_function(
			Z_OBJCE_P(field),
			onphp_ce_DBField TSRMLS_CC
		)
	) {
		zval *tmp;
		
		zend_call_method_with_0_params(
			&field,
			Z_OBJCE_P(field),
			NULL,
			"getfield",
			&tmp
		);
		
		RETURN_ZVAL(tmp, 1, 1);
	} else {
		field = ONPHP_READ_PROPERTY(getThis(), "name");
	}
	
	RETURN_ZVAL(field, 1, 0);
}

ONPHP_METHOD(SelectField, toDialectString)
{
	zval *dialect, *out, *alias;
	
	if (
		zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &dialect)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	zend_call_method_with_1_params(
		&getThis(),
		Z_OBJCE_P(getThis())->parent,
		NULL,
		"todialectstring",
		&out,
		dialect
	);
	
	alias = ONPHP_READ_PROPERTY(getThis(), "alias");
	
	if (
		Z_TYPE_P(alias) != IS_NULL
		&& Z_STRLEN_P(alias)
	) {
		smart_str string = {0};
		
		onphp_append_zval_to_smart_string(&string, out);
		smart_str_appends(&string, " AS ");
		
		zend_call_method_with_1_params(
			&dialect,
			Z_OBJCE_P(dialect),
			NULL,
			"quotefield",
			&alias,
			alias
		);
		
		if (EG(exception)) {
			return;
		}
		
		onphp_append_zval_to_smart_string(&string, alias);
		
		smart_str_0(&string);
		
		RETURN_STRINGL(string.c, string.len, 0);
	} else {
		RETURN_ZVAL(out, 1, 0);
	}
}

static ONPHP_ARGINFO_DIALECT;

static
ZEND_BEGIN_ARG_INFO(arginfo_dialect_string_and_one, 0)
	ZEND_ARG_OBJ_INFO(0, dialect, DialectString, 0)
	ZEND_ARG_INFO(0, alias)
ZEND_END_ARG_INFO()

zend_function_entry onphp_funcs_SelectField[] = {
	ONPHP_ME(SelectField, create, arginfo_dialect_string_and_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(SelectField, __construct, arginfo_dialect_string_and_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(SelectField, getAlias, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(SelectField, getName, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(SelectField, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
