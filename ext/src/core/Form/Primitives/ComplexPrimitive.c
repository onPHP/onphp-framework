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

#include "onphp_core.h"

#include "zend_exceptions.h"

#include "core/Exceptions.h"

#include "core/Form/Primitives/BasePrimitive.h"

ONPHP_METHOD(ComplexPrimitive, __construct)
{
	zval *name, *ternary, *nil;
	zend_class_entry **cep;
	
	ONPHP_GET_ARGS("z", &name);
	
	MAKE_STD_ZVAL(nil);
	ZVAL_NULL(nil);
	
	// will always succeed
	zend_lookup_class("Ternary", strlen("Ternary"), &cep TSRMLS_CC);
	
	ALLOC_INIT_ZVAL(ternary);
	object_init_ex(ternary, *cep);
	Z_TYPE_P(ternary) = IS_OBJECT;
	
	zend_call_method_with_1_params(
		&ternary,
		Z_OBJCE_P(ternary),
		&Z_OBJCE_P(ternary)->constructor,
		"__construct",
		NULL,
		nil
	);
	
	if (EG(exception)) {
		ZVAL_FREE(ternary);
		return;
	}
	
	ONPHP_UPDATE_PROPERTY(getThis(), "single", ternary);
	
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
	
	ONPHP_GET_ARGS("z", &ternary);
	
	ONPHP_CALL_METHOD_0(ternary, "getvalue", &value);
	
	single = ONPHP_READ_PROPERTY(getThis(), "single");
	
	ONPHP_CALL_METHOD_1(single, "setvalue", NULL, value);
	
	RETURN_ZVAL(getThis(), 1, 0);
}

#define COMPLEX_PRIMITIVE_SET_TERNARY_STATE(method, state)			\
	ONPHP_METHOD(ComplexPrimitive, method)							\
	{																\
		zval *single = ONPHP_READ_PROPERTY(getThis(), "single");	\
																	\
		ONPHP_CALL_METHOD_0(single, state, NULL);					\
																	\
		RETURN_ZVAL(getThis(), 1, 0);								\
	}

COMPLEX_PRIMITIVE_SET_TERNARY_STATE(setSingle, "settrue");
COMPLEX_PRIMITIVE_SET_TERNARY_STATE(setComplex, "setfalse");
COMPLEX_PRIMITIVE_SET_TERNARY_STATE(setAnyState, "setnull");

#undef COMPLEX_PRIMITIVE_SET_TERNARY_STATE

ONPHP_METHOD(ComplexPrimitive, import)
{
	zval *scope, *result, *single;
	
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
	
	single = ONPHP_READ_PROPERTY(getThis(), "single");
	
	ONPHP_CALL_METHOD_0(single, "getvalue", &result);
	
	if (Z_TYPE_P(result) == IS_NULL) {
		ONPHP_CALL_METHOD_1(getThis(), "importmarried", &result, scope);
		
		if (!ONPHP_CHECK_EMPTY(result)) {
			ONPHP_CALL_METHOD_1(getThis(), "importsingle", &result, scope);
			
			RETURN_ZVAL(result, 1, 0);
		} else {
			RETURN_TRUE;
		}
	} else if (Z_TYPE_P(result) == IS_BOOL) {
		if (zval_is_true(result)) {
			ONPHP_CALL_METHOD_1(getThis(), "importsingle", &result, scope);
		} else {
			ONPHP_CALL_METHOD_1(getThis(), "importmarried", &result, scope);
		}
		
		RETURN_ZVAL(result, 1, 0);
	}
	
	zend_throw_exception_ex(
		onphp_ce_WrongArgumentException,
		0 TSRMLS_CC,
		"unreachable code reached"
	);
}

ONPHP_METHOD(ComplexPrimitive, exportValue)
{
	zend_throw_exception_ex(
		onphp_ce_UnimplementedFeatureException,
		0 TSRMLS_CC,
		NULL
	);
}

static ONPHP_ARGINFO_ONE;

static
	ZEND_BEGIN_ARG_INFO(arginfo_ternary, 0)
		ZEND_ARG_OBJ_INFO(0, ternary, Ternary, 0)
	ZEND_END_ARG_INFO();

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
