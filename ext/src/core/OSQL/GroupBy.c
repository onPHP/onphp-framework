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

#include "core/Logic/MappableObject.h"
#include "core/Logic/LogicalObject.h"

#include "core/OSQL/FieldTable.h"
#include "core/OSQL/GroupBy.h"

#include "core/Exceptions.h"

ONPHP_METHOD(GroupBy, create)
{
	zval *object, *field;
	
	ONPHP_GET_ARGS("z", &field);
	
	ONPHP_MAKE_OBJECT(GroupBy, object);
	
	ONPHP_CALL_PARENT_1(object, "__construct", NULL, field);
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(GroupBy, toMapped)
{
	zval *object, *dao, *query, *atom, *field;
	
	ONPHP_GET_ARGS("oo", &dao, &query);
	
	field = ONPHP_READ_PROPERTY(getThis(), "field");
	
	ONPHP_CALL_METHOD_2(dao, "guessatom", &atom, field, query);
	
	ONPHP_CALL_STATIC_1_NORET(GroupBy, "create", &object, atom);
	
	ZVAL_FREE(atom);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(GroupBy, toDialectString)
{
	zval
		*out,
		*dialect,
		*field = ONPHP_READ_PROPERTY(getThis(), "field");
	zend_class_entry **cep;
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	ONPHP_FIND_FOREIGN_CLASS("SelectQuery", cep);
	
	if (
		instanceof_function(Z_OBJCE_P(field), *cep TSRMLS_CC)
		|| ONPHP_INSTANCEOF(field, LogicalObject)
	) {
		smart_str string = {0};
		
		smart_str_appendc(&string, '(');
		
		ONPHP_CALL_METHOD_1(dialect, "tofieldstring", &out, field);
		
		onphp_append_zval_to_smart_string(&string, out);
		
		zval_ptr_dtor(&out);
		
		smart_str_appendc(&string, ')');
		
		smart_str_0(&string);
		
		RETURN_STRINGL(string.c, string.len, 0);
	} else {
		ONPHP_CALL_PARENT_1(getThis(), "__construct", &out, dialect);
		
		RETURN_ZVAL(out, 1, 1);
	}
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_TO_MAPPED;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_GroupBy[] = {
	ONPHP_ME(GroupBy, create, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(GroupBy, toMapped, arginfo_to_mapped, ZEND_ACC_PUBLIC)
	ONPHP_ME(GroupBy, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
