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

#include "core/Base/Ternary.h"

#include "core/Form/Primitives/BasePrimitive.h"

ONPHP_METHOD(ComplexPrimitive, __construct)
{
	zval *name, *ternary, *nil;
	
	ONPHP_GET_ARGS("z", &name);
	
	ALLOC_INIT_ZVAL(nil);
	ZVAL_NULL(nil);
	
	ONPHP_MAKE_OBJECT(Ternary, ternary);
	
	ONPHP_CALL_METHOD_1_NORET(ternary, "__construct", NULL, nil);
	
	ZVAL_FREE(nil);
	
	if (EG(exception)) {
		zval_ptr_dtor(&ternary);
		return;
	}
	
	ONPHP_UPDATE_PROPERTY(getThis(), "single", ternary);
	
	zval_ptr_dtor(&ternary);
	
	zend_call_method_with_1_params(
		&getThis(),
		onphp_ce_BasePrimitive,
		&onphp_ce_BasePrimitive->constructor,
		"__construct",
		NULL,
		name
	);
	
	if (EG(exception)) {
		return;
	}
}

ONPHP_GETTER(ComplexPrimitive, getState, single);

ONPHP_METHOD(ComplexPrimitive, setState)
{
	zval *ternary, *single, *value;
	
	ONPHP_GET_ARGS("o", &ternary);
	
	ONPHP_CALL_METHOD_0(ternary, "getvalue", &value);
	
	single = ONPHP_READ_PROPERTY(getThis(), "single");
	
	ONPHP_CALL_METHOD_1_NORET(single, "setvalue", NULL, value);
	
	zval_ptr_dtor(&value);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_THIS;
}

#define COMPLEX_PRIMITIVE_SET_TERNARY_STATE(method, state)			\
	ONPHP_METHOD(ComplexPrimitive, method)							\
	{																\
		zval *single = ONPHP_READ_PROPERTY(getThis(), "single");	\
																	\
		ONPHP_CALL_METHOD_0(single, state, NULL);					\
																	\
		RETURN_THIS;												\
	}

COMPLEX_PRIMITIVE_SET_TERNARY_STATE(setSingle, "settrue");
COMPLEX_PRIMITIVE_SET_TERNARY_STATE(setComplex, "setfalse");
COMPLEX_PRIMITIVE_SET_TERNARY_STATE(setAnyState, "setnull");

#undef COMPLEX_PRIMITIVE_SET_TERNARY_STATE

ONPHP_METHOD(ComplexPrimitive, import)
{
	zval *scope, *result, *single;
	
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
		return;
	}
	
	if (!ONPHP_CHECK_EMPTY(result)) {
		zval_ptr_dtor(&result);
		RETURN_NULL();
	}
	
	zval_ptr_dtor(&result);
	
	single = ONPHP_READ_PROPERTY(getThis(), "single");
	
	ONPHP_CALL_METHOD_0(single, "getvalue", &result);
	
	if (Z_TYPE_P(result) == IS_NULL) {
		zval_ptr_dtor(&result);
		
		ONPHP_CALL_METHOD_1(getThis(), "importmarried", &result, scope);
		
		if (!ONPHP_CHECK_EMPTY(result)) {
			zval_ptr_dtor(&result);
			
			ONPHP_CALL_METHOD_1(getThis(), "importsingle", &result, scope);
			
			RETURN_ZVAL(result, 1, 1);
		} else {
			zval_ptr_dtor(&result);
			RETURN_TRUE;
		}
	} else if (Z_TYPE_P(result) == IS_BOOL) {
		if (zval_is_true(result)) {
			zval_ptr_dtor(&result);
			ONPHP_CALL_METHOD_1(getThis(), "importsingle", &result, scope);
		} else {
			zval_ptr_dtor(&result);
			ONPHP_CALL_METHOD_1(getThis(), "importmarried", &result, scope);
		}
		
		RETURN_ZVAL(result, 1, 1);
	}
	
	ONPHP_THROW(WrongArgumentException, "unreachable code reached");
}

ONPHP_METHOD(ComplexPrimitive, exportValue)
{
	ONPHP_THROW(UnimplementedFeatureException, NULL);
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_TERNARY;

zend_function_entry onphp_funcs_ComplexPrimitive[] = {
	ONPHP_ME(ComplexPrimitive, __construct, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(ComplexPrimitive, getState, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(ComplexPrimitive, setState, arginfo_ternary, ZEND_ACC_PUBLIC)
	ONPHP_ME(ComplexPrimitive, setSingle, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(ComplexPrimitive, setComplex, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(ComplexPrimitive, setAnyState, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ABSTRACT_ME(ComplexPrimitive, importSingle, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ABSTRACT_ME(ComplexPrimitive, importMarried, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(ComplexPrimitive, import, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(ComplexPrimitive, exportValue, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
