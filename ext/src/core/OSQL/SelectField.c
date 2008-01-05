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
#include "core/OSQL/DBField.h"
#include "core/OSQL/DialectString.h"
#include "core/OSQL/SelectField.h"

ONPHP_METHOD(SelectField, create)
{
	zval *field, *alias, *object;
	
	ONPHP_GET_ARGS("Oz", &field, onphp_ce_DialectString, &alias);
	
	ONPHP_MAKE_OBJECT(SelectField, object);
	
	ONPHP_CALL_METHOD_1_NORET(object, "__construct", NULL, alias);
	
	if (EG(exception)) {
		ZVAL_FREE(object);
		return;
	}
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(SelectField, __construct)
{
	zval *field, *alias;
	
	ONPHP_GET_ARGS("Oz", &field, onphp_ce_DialectString, &alias);
	
	ONPHP_UPDATE_PROPERTY(getThis(), "alias", alias);
	
	ONPHP_CALL_PARENT_1(getThis(), "__construct", NULL, field);
}

ONPHP_GETTER(SelectField, getAlias, alias);

ONPHP_METHOD(SelectField, getName)
{
	zval *field = ONPHP_READ_PROPERTY(getThis(), "field");
	
	if (ONPHP_INSTANCEOF(field, DBField)) {
		zval *tmp;
		
		ONPHP_CALL_METHOD_0(field, "getfield", &tmp);
		
		RETURN_ZVAL(tmp, 1, 1);
	} else {
		field = ONPHP_READ_PROPERTY(getThis(), "name");
		
		RETURN_ZVAL(field, 1, 0);
	}
}

ONPHP_METHOD(SelectField, toDialectString)
{
	zval *dialect, *out, *alias;
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	ONPHP_CALL_PARENT_1(getThis(), "todialectstring", &out, dialect);
	
	alias = ONPHP_READ_PROPERTY(getThis(), "alias");
	
	if (
		(Z_TYPE_P(alias) != IS_NULL)
		&& Z_STRLEN_P(alias)
	) {
		smart_str string = {0};
		
		onphp_append_zval_to_smart_string(&string, out);
		
		zval_ptr_dtor(&out);
		
		smart_str_appendl(&string, " AS ", 4);
		
		ONPHP_CALL_METHOD_1(dialect, "quotefield", &alias, alias);
		
		onphp_append_zval_to_smart_string(&string, alias);
		
		zval_ptr_dtor(&alias);
		
		smart_str_0(&string);
		
		RETURN_STRINGL(string.c, string.len, 0);
	} else {
		RETURN_ZVAL(out, 1, 1);
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
	ONPHP_ME(SelectField, __construct, arginfo_dialect_string_and_one, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(SelectField, getAlias, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(SelectField, getName, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(SelectField, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
