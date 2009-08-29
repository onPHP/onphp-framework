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
#include "onphp_util.h"

#include "ext/standard/php_string.h"

#include "core/DB/Dialect.h"
#include "core/OSQL/DBValue.h"
#include "core/OSQL/DialectString.h"
#include "core/OSQL/Query.h"
#include "core/Exceptions.h"

ONPHP_METHOD(Dialect, quoteField)
{
	zval *field;
	smart_str string = {0};
	
	ONPHP_GET_ARGS("z", &field);
	
	smart_str_appendc(&string, '"');
	onphp_append_zval_to_smart_string(&string, field);
	smart_str_appendc(&string, '"');
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(Dialect, quoteTable)
{
	zval *table;
	smart_str string = {0};
	
	ONPHP_GET_ARGS("z", &table);
	
	smart_str_appendc(&string, '"');
	onphp_append_zval_to_smart_string(&string, table);
	smart_str_appendc(&string, '"');
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(Dialect, toCasted)
{
	zval *field, *type;
	smart_str string = {0};
	
	ONPHP_GET_ARGS("zz", &field, &type);
	
	smart_str_appendl(&string, "CAST (", 6);
	onphp_append_zval_to_smart_string(&string, field);
	smart_str_appendl(&string, " AS ", 4);
	onphp_append_zval_to_smart_string(&string, type);
	smart_str_appendc(&string, ')');
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(Dialect, timeZone)
{
	zend_bool exist = 0;
	
	ONPHP_GET_ARGS("|b", &exist);
	
	if (exist) {
		RETURN_STRING(" WITH TIME ZONE", 1);
	}
	
	RETURN_STRING(" WITHOUT TIME ZONE", 1);
}

ONPHP_METHOD(Dialect, dropTableMode)
{
	zend_bool cascade = 0;
	
	ONPHP_GET_ARGS("|b", &cascade);
	
	if (cascade) {
		RETURN_STRING(" CASCADE", 1);
	}
	
	RETURN_STRING(" RESTRICT", 1);
}

ONPHP_METHOD(Dialect, quoteBinary)
{
	zval *data, *out;
	
	ONPHP_GET_ARGS("z", &data);
	
	ONPHP_CALL_METHOD_1(getThis(), "quotevalue", &out, data);
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Dialect, unquoteBinary)
{
	zval *data;
	
	ONPHP_GET_ARGS("z", &data);
	
	RETURN_ZVAL(data, 1, 0);
}

ONPHP_METHOD(Dialect, typeToString)
{
	zval *type, *out;
	
	ONPHP_GET_ARGS("z", &type);
	
	ONPHP_CALL_METHOD_0(type, "getname", &out);
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Dialect, fieldToString)
{
	zval *field, *out;
	
	ONPHP_GET_ARGS("z", &field);
	
	if (ONPHP_INSTANCEOF(field, DialectString)) {
		ONPHP_CALL_METHOD_1(field, "todialectstring", &out, getThis());
	} else {
		ONPHP_CALL_METHOD_1(getThis(), "quotefield", &out, field);
	}
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Dialect, valueToString)
{
	zval *value, *out;
	
	ONPHP_GET_ARGS("z", &value);
	
	if (ONPHP_INSTANCEOF(value, DBValue)) {
		ONPHP_CALL_METHOD_1(value, "todialectstring", &out, getThis());
	} else {
		ONPHP_CALL_METHOD_1(getThis(), "quotevalue", &out, value);
	}
	
	RETURN_ZVAL(out, 1, 1);
}

#define ONPHP_DIALECT_TO_NEEDED_STRING(method_name)							\
	smart_str string = {0};													\
	zval *exp, *out;														\
																			\
	ONPHP_GET_ARGS("z", &exp);												\
																			\
	if (Z_TYPE_P(exp) == IS_NULL) {											\
		RETURN_NULL();														\
	}																		\
																			\
	if (ONPHP_INSTANCEOF(exp, DialectString)) {								\
		ONPHP_CALL_METHOD_1(exp, "todialectstring", &out, getThis());		\
																			\
		if (ONPHP_INSTANCEOF(exp, Query)) {									\
			smart_str_appendc(&string, '(');								\
			onphp_append_zval_to_smart_string(&string, out);				\
			smart_str_appendc(&string, ')');								\
		} else {															\
			onphp_append_zval_to_smart_string(&string, out);				\
		}																	\
																			\
	} else {																\
		ONPHP_CALL_METHOD_1(getThis(), method_name, &out, exp);				\
																			\
		onphp_append_zval_to_smart_string(&string, out);					\
	}																		\
																			\
	zval_ptr_dtor(&out);													\
																			\
	smart_str_0(&string);													\
																			\
	RETURN_STRINGL(string.c, string.len, 0);

ONPHP_METHOD(Dialect, toFieldString)
{
	ONPHP_DIALECT_TO_NEEDED_STRING("quotefield");
}

ONPHP_METHOD(Dialect, toValueString)
{
	ONPHP_DIALECT_TO_NEEDED_STRING("quotevalue");
}

#undef ONPHP_DIALECT_TO_NEEDED_STRING

ONPHP_METHOD(Dialect, fullTextSearch)
{
	ONPHP_THROW(UnimplementedFeatureException, "implement me first");
}

ONPHP_METHOD(Dialect, fullTextRank)
{
	ONPHP_THROW(UnimplementedFeatureException, "implement me first");
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_TWO;
static ONPHP_ARGINFO_THREE;
static ONPHP_ARGINFO_DBCOLUMN;
static ONPHP_ARGINFO_DATATYPE;

zend_function_entry onphp_funcs_Dialect[] = {
	ONPHP_ABSTRACT_ME(Dialect, preAutoincrement, arginfo_dbcolumn, ZEND_ACC_PUBLIC)
	ONPHP_ABSTRACT_ME(Dialect, postAutoincrement, arginfo_dbcolumn, ZEND_ACC_PUBLIC)
	ONPHP_ABSTRACT_ME(Dialect, hasTruncate, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ABSTRACT_ME(Dialect, hasMultipleTruncate, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, quoteField, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, quoteTable, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, toCasted, arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, timeZone, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, dropTableMode, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, quoteBinary, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, unquoteBinary, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, typeToString, arginfo_datatype, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, toFieldString, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, toValueString, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, fieldToString, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, valueToString, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, fullTextSearch, arginfo_three, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, fullTextRank, arginfo_three, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
