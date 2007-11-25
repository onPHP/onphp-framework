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

#include "core/Base/Singleton.h"
#include "core/DB/Dialect.h"
#include "core/DB/ImaginaryDialect.h"
#include "core/OSQL/DBValue.h"
#include "core/OSQL/DialectString.h"

#if (PHP_MAJOR_VERSION == 5) && (PHP_MINOR_VERSION < 2)
#define onphp_implode(glue, words, copy) php_implode(glue, words, copy)
#else
#define onphp_implode(glue, words, copy) php_implode(glue, words, copy TSRMLS_CC)
#endif

ONPHP_METHOD(ImaginaryDialect, me)
{
	zval *instance, *class;
	
	ALLOC_INIT_ZVAL(class);
	ZVAL_STRING(class, onphp_ce_ImaginaryDialect->name, 1);
	
	ONPHP_CALL_STATIC_1_NORET(Singleton, "getinstance", &instance, class);
	
	ZVAL_FREE(class);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(instance, 1, 1);
}

ONPHP_METHOD(ImaginaryDialect, preAutoincrement)
{
	RETURN_NULL();
}

ONPHP_METHOD(ImaginaryDialect, postAutoincrement)
{
	RETURN_STRING("AUTOINCREMENT", 1);
}

ONPHP_METHOD(ImaginaryDialect, hasTruncate)
{
	RETURN_FALSE;
}

ONPHP_METHOD(ImaginaryDialect, hasMultipleTruncate)
{
	RETURN_FALSE;
}

ONPHP_METHOD(ImaginaryDialect, quoteValue)
{
	zval *value;
	
	ONPHP_GET_ARGS("z", &value);
	
	RETURN_ZVAL(value, 1, 0);
}

ONPHP_METHOD(ImaginaryDialect, quoteField)
{
	zval *field;
	
	ONPHP_GET_ARGS("z", &field);
	
	RETURN_ZVAL(field, 1, 0);
}

ONPHP_METHOD(ImaginaryDialect, quoteTable)
{
	zval *table;
	
	ONPHP_GET_ARGS("z", &table);
	
	RETURN_ZVAL(table, 1, 0);
}

ONPHP_METHOD(ImaginaryDialect, fieldToString)
{
	zval *field, *out;
	
	ONPHP_GET_ARGS("z", &field);
	
	if (ONPHP_INSTANCEOF(field, DialectString)) {
		ONPHP_CALL_METHOD_1(field, "todialectstring", &out, getThis());
		
		RETURN_ZVAL(out, 1, 1);
	} else {
		RETURN_ZVAL(field, 1, 0);
	}
}

ONPHP_METHOD(ImaginaryDialect, valueToString)
{
	zval *value;
	
	ONPHP_GET_ARGS("z", &value);
	
	if (ONPHP_INSTANCEOF(value, DBValue)) {
		zval *out;
		
		ONPHP_CALL_METHOD_1(value, "todialectstring", &out, getThis());
		
		RETURN_ZVAL(out, 1, 1);
	}
	
	RETURN_ZVAL(value, 1, 0);
}

ONPHP_METHOD(ImaginaryDialect, fullTextSearch)
{
	smart_str out = {0};
	zval *field, *words, *copy, *glue;
	long logic;
	
	ONPHP_GET_ARGS("zzl", &field, &words, &logic);
	
	ALLOC_INIT_ZVAL(glue);
	ALLOC_INIT_ZVAL(copy);
	
	ZVAL_ZVAL(copy, words, 1, 0);
	
	if (logic == 1) {
		ZVAL_STRINGL(glue, " & ", 3, 1);
	} else {
		ZVAL_STRINGL(glue, " | ", 3, 1);
	}
	
	onphp_implode(glue, words, copy);
	
	smart_str_appendl(&out, "(\"", 2);
	
	if (ONPHP_INSTANCEOF(field, DialectString)) {
		zval *string;
		
		ONPHP_CALL_METHOD_1_NORET(getThis(), "fieldtostring", &string, field);
		
		if (EG(exception)) {
			goto out;
		}
		
		onphp_append_zval_to_smart_string(&out, string);
		zval_dtor(string);
	} else {
		onphp_append_zval_to_smart_string(&out, field);
	}
	
	smart_str_appendl(&out, "\" CONTAINS \"", 12);
	smart_str_appendl(&out, Z_STRVAL_P(copy), Z_STRLEN_P(copy));
	smart_str_appendl(&out, "\")", 2);
	smart_str_0(&out);
	
	RETVAL_STRINGL(out.c, out.len, 0);
	
out:
	ZVAL_FREE(glue);
	ZVAL_FREE(copy);
}

ONPHP_METHOD(ImaginaryDialect, fullTextRank)
{
	smart_str out = {0};
	zval *field, *words, *copy, *glue;
	long logic;
	
	ONPHP_GET_ARGS("zzl", &field, &words, &logic);
	
	ALLOC_INIT_ZVAL(glue);
	ALLOC_INIT_ZVAL(copy);
	
	ZVAL_ZVAL(copy, words, 1, 0);
	
	if (logic == 1) {
		ZVAL_STRING(glue, " & ", 1);
	} else {
		ZVAL_STRING(glue, " | ", 1);
	}
	
	onphp_implode(glue, words, copy);
	
	smart_str_appendl(&out, "(RANK BY \"", 10);
	
	if (ONPHP_INSTANCEOF(field, DialectString)) {
		zval *string;
		
		ONPHP_CALL_METHOD_1_NORET(getThis(), "fieldtostring", &string, field);
		
		if (EG(exception)) {
			goto out;
		}
		
		onphp_append_zval_to_smart_string(&out, string);
		zval_dtor(string);
	} else {
		onphp_append_zval_to_smart_string(&out, field);
	}
	
	smart_str_appendl(&out, "\" WHICH CONTAINS \"", 18);
	smart_str_appendl(&out, Z_STRVAL_P(copy), Z_STRLEN_P(copy));
	smart_str_appendl(&out, "\")", 2);
	smart_str_0(&out);
	
	RETVAL_STRINGL(out.c, out.len, 0);
	
out:
	ZVAL_FREE(glue);
	ZVAL_FREE(copy);
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_THREE;
static ONPHP_ARGINFO_DBCOLUMN;

zend_function_entry onphp_funcs_ImaginaryDialect[] = {
	ONPHP_ME(ImaginaryDialect, me,	NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(ImaginaryDialect, preAutoincrement, arginfo_dbcolumn, ZEND_ACC_PUBLIC)
	ONPHP_ME(ImaginaryDialect, postAutoincrement, arginfo_dbcolumn, ZEND_ACC_PUBLIC)
	ONPHP_ME(ImaginaryDialect, hasTruncate, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(ImaginaryDialect, hasMultipleTruncate, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(ImaginaryDialect, quoteValue, arginfo_one, ZEND_ACC_PUBLIC |  ZEND_ACC_STATIC)
	ONPHP_ME(ImaginaryDialect, quoteField, arginfo_one, ZEND_ACC_PUBLIC |  ZEND_ACC_STATIC)
	ONPHP_ME(ImaginaryDialect, quoteTable, arginfo_one, ZEND_ACC_PUBLIC |  ZEND_ACC_STATIC)
	ONPHP_ME(ImaginaryDialect, fieldToString, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(ImaginaryDialect, valueToString, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(ImaginaryDialect, fullTextSearch, arginfo_three, ZEND_ACC_PUBLIC)
	ONPHP_ME(ImaginaryDialect, fullTextRank, arginfo_three, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
