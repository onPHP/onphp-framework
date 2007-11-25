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
	
	ONPHP_CALL_METHOD_2_NORET(object, "__construct", NULL, what, from);
	
	if (EG(exception)) {
		ZVAL_FREE(object);
		return;
	}
	
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
			ONPHP_THROW(WrongArgumentException, NULL);
		}
	}
	
	ONPHP_MAKE_OBJECT(DBField, fromField);
	
	ONPHP_CALL_METHOD_1_NORET(fromField, "__construct", NULL, from);
	
	if (EG(exception)) {
		ZVAL_FREE(fromField);
		return;
	}
	
	ONPHP_UPDATE_PROPERTY(getThis(), "from", fromField);
	
	zval_ptr_dtor(&fromField);
	
	ONPHP_FIND_FOREIGN_CLASS("DatePart", cep);
	
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
		
		ONPHP_CALL_METHOD_1_NORET(whatPart, "__construct", NULL, what);
		
		if (EG(exception)) {
			ZVAL_FREE(whatPart);
			return;
		}
		
		ONPHP_UPDATE_PROPERTY(getThis(), "what", whatPart);
		
		zval_ptr_dtor(&whatPart);
	} else {
		ONPHP_UPDATE_PROPERTY(getThis(), "what", what);
		
		zval_ptr_dtor(&what);
	}
}

ONPHP_METHOD(ExtractPart, toMapped)
{
	zval *dao, *query, *what, *from, *atom;
	
	ONPHP_GET_ARGS("oo", &dao, &query);
		
	what = ONPHP_READ_PROPERTY(getThis(), "what");
	from = ONPHP_READ_PROPERTY(getThis(), "from");
	
	ONPHP_CALL_METHOD_2(dao, "guessatom", &atom, from, query);
	
	ONPHP_CALL_STATIC_2_NORET(ExtractPart, "create", &query, what, atom);
	
	zval_ptr_dtor(&atom);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(query, 1, 1);
}

ONPHP_METHOD(ExtractPart, toDialectString)
{
	zval *dialect, *what, *from, *whatString, *fromString;
	smart_str string = {0};
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	what = ONPHP_READ_PROPERTY(getThis(), "what");
	from = ONPHP_READ_PROPERTY(getThis(), "from");
	
	ONPHP_CALL_METHOD_0(what, "tostring", &whatString);
	
	ONPHP_CALL_METHOD_1_NORET(from, "todialectstring", &fromString, dialect);
	
	if (EG(exception)) {
		ZVAL_FREE(whatString);
		return;
	}
	
	smart_str_appendl(&string, "EXTRACT(", 8);
	onphp_append_zval_to_smart_string(&string, whatString);
	smart_str_appendl(&string, " FROM ", 6);
	onphp_append_zval_to_smart_string(&string, fromString);
	smart_str_appendc(&string, ')');
	smart_str_0(&string);
	
	zval_ptr_dtor(&whatString);
	zval_ptr_dtor(&fromString);
	
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
