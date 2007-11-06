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

#include "core/Logic/MappableObject.h"

#include "core/OSQL/DBField.h"
#include "core/OSQL/DBValue.h"
#include "core/OSQL/DialectString.h"
#include "core/OSQL/ExtractPart.h"

ONPHP_METHOD(ExtractPart, create)
{
	zval *object, *what, *from;
	
	ONPHP_GET_ARGS("zz", &what, &from);
	
	ONPHP_MAKE_OBJECT(ExtractPart, object);
	
	zend_call_method_with_2_params(
		&object,
		Z_OBJCE_P(object),
		&Z_OBJCE_P(object)->constructor,
		"__construct",
		NULL,
		what,
		from
	);
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(ExtractPart, __construct)
{
	zval *what, *from, *fromField;
	zend_class_entry **cep;
	
	ONPHP_GET_ARGS("zz", &what, &from);
	
	if (ONPHP_INSTANCEOF(from, DialectString)) {
		if (
			!(
				ONPHP_INSTANCEOF(from, DBValue)
				|| ONPHP_INSTANCEOF(from, DBField)
			)
		) {
			zend_throw_exception_ex(
				onphp_ce_WrongArgumentException,
				0 TSRMLS_CC,
				NULL
			);
			
			return;
		}
	}
	
	ONPHP_MAKE_OBJECT(DBField, fromField);
	
	zend_call_method_with_1_params(
		&fromField,
		Z_OBJCE_P(fromField),
		&Z_OBJCE_P(fromField)->constructor,
		"__construct",
		NULL,
		from
	);
	
	if (EG(exception)) {
		ZVAL_FREE(fromField);
		return;
	}
	
	ONPHP_UPDATE_PROPERTY(getThis(), "from", fromField);
	
	if (
		zend_lookup_class("DatePart", strlen("DatePart"), &cep TSRMLS_CC)
		== FAILURE
	) {
		zend_throw_exception_ex(
			onphp_ce_ClassNotFoundException,
			0 TSRMLS_CC,
			"DatePart"
		);
		return;
	}
	
	if (
		!(
			(Z_TYPE_P(what) == IS_OBJECT)
			&& instanceof_function(Z_OBJCE_P(what), *cep TSRMLS_CC)
		)
	) {
		zval *whatPart;
		
		ALLOC_INIT_ZVAL(whatPart);
		object_init_ex(whatPart, *cep);
		Z_TYPE_P(whatPart) = IS_OBJECT;
		
		zend_call_method_with_1_params(
			&whatPart,
			Z_OBJCE_P(whatPart),
			&Z_OBJCE_P(whatPart)->constructor,
			"__construct",
			NULL,
			what
		);
		
		if (EG(exception)) {
			ZVAL_FREE(whatPart);
			return;
		}
		
		ONPHP_UPDATE_PROPERTY(getThis(), "what", whatPart);
	} else {
		ONPHP_UPDATE_PROPERTY(getThis(), "what", what);
	}
}

ONPHP_METHOD(ExtractPart, toMapped)
{
	zval *dao, *query, *what, *from, *atom;
	
	ONPHP_GET_ARGS("zz", &dao, &query);
	
	what = ONPHP_READ_PROPERTY(getThis(), "what");
	from = ONPHP_READ_PROPERTY(getThis(), "from");
	
	ONPHP_CALL_METHOD_2(dao, "guessatom", &atom, from, query);
	
	zend_call_method_with_2_params(
		NULL,
		Z_OBJCE_P(getThis()),
		NULL,
		"create",
		&query,
		what,
		atom
	);
	
	RETURN_ZVAL(query, 1, 0);
}

ONPHP_METHOD(ExtractPart, toDialectString)
{
	zval *dialect, *what, *from, *whatString, *fromString;
	smart_str string = {0};
	
	ONPHP_GET_ARGS("z", &dialect);
	
	what = ONPHP_READ_PROPERTY(getThis(), "what");
	from = ONPHP_READ_PROPERTY(getThis(), "from");
	
	ONPHP_CALL_METHOD_0(what, "tostring", &whatString);
	
	ONPHP_CALL_METHOD_1(from, "todialectstring", &fromString, dialect);
	
	smart_str_appends(&string, "EXTRACT(");
	onphp_append_zval_to_smart_string(&string, whatString);
	smart_str_appends(&string, " FROM ");
	onphp_append_zval_to_smart_string(&string, fromString);
	smart_str_appends(&string, ")");
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}


static ONPHP_ARGINFO_TWO;
static ONPHP_ARGINFO_TO_MAPPED;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_ExtractPart[] = {
	ONPHP_ME(ExtractPart, create, arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(ExtractPart, __construct, arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(ExtractPart, toMapped, arginfo_to_mapped, ZEND_ACC_PUBLIC)
	ONPHP_ME(ExtractPart, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
