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

#include "core/Exceptions.h"

#include "core/Form/Primitives/BasePrimitive.h"

ONPHP_METHOD(PrimitiveNumber, import)
{
	zval *scope, *result, *name, *value, *min, *max, *out;
	
	ONPHP_GET_ARGS("a", &scope);
	
	zend_call_method_with_1_params(
		&getThis(),
		onphp_ce_BasePrimitive,
		NULL,
		"import",
		&result,
		scope
	);
	
	if (EG(exception)) {
		zval_ptr_dtor(&result);
		return;
	}
	
	if (!ONPHP_CHECK_EMPTY(result)) {
		zval_ptr_dtor(&result);
		RETURN_NULL();
	}
	
	zval_ptr_dtor(&result);
	
	name = ONPHP_READ_PROPERTY(getThis(), "name");
	
	ONPHP_ASSOC_GET(scope, Z_STRVAL_P(name), value);
	
	ONPHP_CALL_METHOD_1_NORET(getThis(), "checknumber", NULL, value);
	
	if (EG(exception)) {
		zend_clear_exception(TSRMLS_C);
		RETURN_FALSE;
	}
	
	ONPHP_CALL_METHOD_1(getThis(), "castnumber", &out, value);
	
	ONPHP_CALL_METHOD_0(getThis(), "selffilter", NULL);
	
	if (
		(Z_TYPE_P(out) == IS_LONG)
		&& (min = ONPHP_READ_PROPERTY(getThis(), "min"))
		&& (max = ONPHP_READ_PROPERTY(getThis(), "max"))
		&& !(
			(IS_NULL != Z_TYPE_P(min))
			&& (Z_LVAL_P(out) < Z_LVAL_P(min))
		) && !(
			(IS_NULL != Z_TYPE_P(max))
			&& (Z_LVAL_P(out) > Z_LVAL_P(max))
		)
	) {
		ONPHP_UPDATE_PROPERTY_LONG(getThis(), "value", Z_LVAL_P(out));
		
		RETVAL_TRUE;
	} else {
		RETVAL_FALSE;
	}
	
	zval_ptr_dtor(&out);
}

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_PrimitiveNumber[] = {
	ONPHP_ABSTRACT_ME(PrimitiveNumber, checkNumber, arginfo_one, ZEND_ACC_PROTECTED)
	ONPHP_ABSTRACT_ME(PrimitiveNumber, castNumber, arginfo_one, ZEND_ACC_PROTECTED)
	ONPHP_ME(PrimitiveNumber, import, arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
