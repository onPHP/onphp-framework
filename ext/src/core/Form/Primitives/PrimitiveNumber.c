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
	zval *scope, *result, *name, *value, *min, *max;
	
	ONPHP_GET_ARGS("z", &scope);
	
	zend_call_method_with_1_params(
		&getThis(),
		onphp_ce_BasePrimitive,
		NULL,
		"import",
		&result,
		scope
	);
	
	if (EG(exception)) {
		return;
	}
	
	if (!ONPHP_CHECK_EMPTY(result)) {
		RETURN_NULL();
	}
	
	name = ONPHP_READ_PROPERTY(getThis(), "name");
	
	ONPHP_ASSOC_GET(scope, name, value);
	
	zend_try {
		ONPHP_CALL_METHOD_1_NORET(getThis(), "checknumber", NULL, value);
	} zend_catch {
		ZVAL_FREE(value);
		RETURN_FALSE;
	} zend_end_try();
	
	ONPHP_CALL_METHOD_1(getThis(), "castnumber", &value, value);
	
	ONPHP_CALL_METHOD_0(getThis(), "selffilter", NULL);
	
	min = ONPHP_READ_PROPERTY(getThis(), "min");
	max = ONPHP_READ_PROPERTY(getThis(), "max");
	
	if (
		!(
			(IS_NULL != Z_TYPE_P(min))
			&& (Z_LVAL_P(value) < Z_LVAL_P(min))
		) && !(
			(IS_NULL != Z_TYPE_P(max))
			&& (Z_LVAL_P(value) > Z_LVAL_P(max))
		)
	) {
		ONPHP_UPDATE_PROPERTY(getThis(), "value", value);
		
		RETVAL_TRUE;
	} else {
		RETVAL_FALSE;
	}
	
	zval_ptr_dtor(&value);
}

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_PrimitiveNumber[] = {
	ONPHP_ABSTRACT_ME(PrimitiveNumber, checkNumber, arginfo_one, ZEND_ACC_PROTECTED)
	ONPHP_ABSTRACT_ME(PrimitiveNumber, castNumber, arginfo_one, ZEND_ACC_PROTECTED)
	ONPHP_ME(PrimitiveNumber, import, arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
