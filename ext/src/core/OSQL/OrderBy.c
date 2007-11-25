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

#include "core/Base/Ternary.h"

#include "core/DB/Dialect.h"

#include "core/OSQL/OrderBy.h"

#include "core/Logic/MappableObject.h"
#include "core/Logic/LogicalObject.h"

#include "core/Exceptions.h"

ONPHP_METHOD(OrderBy, create)
{
	zval *object, *field;
	
	ONPHP_MAKE_OBJECT(OrderBy, object);
	
	ONPHP_GET_ARGS("z", &field);
	
	ONPHP_CALL_METHOD_1_NORET(object, "__construct", NULL, field);
	
	if (EG(exception)) {
		ZVAL_FREE(object);
		return;
	}
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(OrderBy, __construct)
{
	zval *field, *direction;
	
	ONPHP_GET_ARGS("z", &field);
	
	ONPHP_CALL_PARENT_1(getThis(), "__construct", NULL, field);
	
	ONPHP_MAKE_OBJECT(Ternary, direction);
	
	ONPHP_UPDATE_PROPERTY_NULL(direction, "trinity");
	
	ONPHP_UPDATE_PROPERTY(getThis(), "direction", direction);
	
	zval_ptr_dtor(&direction);
}

ONPHP_METHOD(OrderBy, desc)
{
	ONPHP_UPDATE_PROPERTY_BOOL(
		ONPHP_READ_PROPERTY(getThis(), "direction"),
		"trinity",
		0
	);
}

ONPHP_METHOD(OrderBy, asc)
{
	ONPHP_UPDATE_PROPERTY_BOOL(
		ONPHP_READ_PROPERTY(getThis(), "direction"),
		"trinity",
		1
	);
}

ONPHP_METHOD(OrderBy, isAsc)
{
	zval *trinity = ONPHP_READ_PROPERTY(
		ONPHP_READ_PROPERTY(getThis(), "direction"),
		"trinity"
	);
	
	RETURN_BOOL(
		(Z_TYPE_P(trinity) == IS_BOOL)
		&& zval_is_true(trinity)
	);
}

ONPHP_METHOD(OrderBy, invert)
{
	zval *trinity = ONPHP_READ_PROPERTY(
		ONPHP_READ_PROPERTY(getThis(), "direction"),
		"trinity"
	);
	
	if (
		(Z_TYPE_P(trinity) == IS_BOOL)
		&& zval_is_true(trinity)
	) {
		ZVAL_BOOL(trinity, 0);
	} else {
		Z_TYPE_P(trinity) = IS_BOOL;
		ZVAL_BOOL(trinity, 1);
	}
	
	RETURN_THIS;
}

ONPHP_METHOD(OrderBy, toMapped)
{
	zval
		*dao,
		*query,
		*order,
		*atom,
		*field = ONPHP_READ_PROPERTY(getThis(), "field"),
		*trinity = ONPHP_READ_PROPERTY(
			ONPHP_READ_PROPERTY(getThis(), "direction"),
			"trinity"
		);
	
	ONPHP_GET_ARGS("oo", &dao, &query);
	
	ONPHP_CALL_METHOD_2(dao, "guessatom", &atom, field, query);
	
	ONPHP_MAKE_OBJECT(OrderBy, order);
	
	ONPHP_CALL_METHOD_1_NORET(order, "__construct", NULL, atom);
	
	if (EG(exception)) {
		ZVAL_FREE(order);
		zval_ptr_dtor(&atom);
		return;
	}
	
	if (Z_TYPE_P(trinity) == IS_BOOL) {
		if (zval_is_true(trinity)) {
			ONPHP_CALL_METHOD_0_NORET(order, "asc", NULL);
		} else {
			ONPHP_CALL_METHOD_0_NORET(order, "desc", NULL);
		}
	}
	
	zval_ptr_dtor(&atom);
	
	if (EG(exception)) {
		ZVAL_FREE(order);
		return;
	}
	
	RETURN_ZVAL(order, 1, 1);
}

ONPHP_METHOD(OrderBy, toDialectString)
{
	zval
		*dialect,
		*field = ONPHP_READ_PROPERTY(getThis(), "field"),
		*trinity = ONPHP_READ_PROPERTY(
			ONPHP_READ_PROPERTY(getThis(), "direction"),
			"trinity"
		),
		*out;
	
	zend_class_entry **sq;
	
	smart_str string = {0};
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	ONPHP_FIND_FOREIGN_CLASS("SelectQuery", sq);
	
	if (
		instanceof_function(Z_OBJCE_P(field), *sq TSRMLS_CC)
		|| ONPHP_INSTANCEOF(field, LogicalObject)
	) {
		smart_str_appendc(&string, '(');
		
		ONPHP_CALL_METHOD_1(dialect, "fieldtostring", &out, field);
		
		onphp_append_zval_to_smart_string(&string, out);
		
		zval_ptr_dtor(&out);
		
		smart_str_appendc(&string, ')');
	} else {
		ONPHP_CALL_PARENT_1(getThis(), "todialectstring", &out, dialect);
		
		onphp_append_zval_to_smart_string(&string, out);
		
		zval_ptr_dtor(&out);
	}
	
	if (Z_TYPE_P(trinity) == IS_BOOL) {
		if (zval_is_true(trinity)) {
			smart_str_appendl(&string, " ASC", 4);
		} else {
			smart_str_appendl(&string, " DESC", 5);
		}
	}
	
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_TO_MAPPED;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_OrderBy[] = {
	ONPHP_ME(OrderBy, create, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(OrderBy, __construct, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(OrderBy, desc, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(OrderBy, asc, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(OrderBy, isAsc, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(OrderBy, invert, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(OrderBy, toMapped, arginfo_to_mapped, ZEND_ACC_PUBLIC)
	ONPHP_ME(OrderBy, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
