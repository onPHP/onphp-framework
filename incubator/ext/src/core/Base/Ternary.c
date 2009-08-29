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

#include "core/Base/Ternary.h"

#include "core/Exceptions.h"

#define ONPHP_TERNARY_SET_VALUE(ternary)						\
	if (ZEND_NUM_ARGS() == 0) {									\
		ONPHP_UPDATE_PROPERTY_NULL(ternary, "trinity");			\
																\
		goto out;												\
	} else {													\
		zval *value;											\
																\
		ONPHP_GET_ARGS("z", &value);							\
																\
																\
		if (Z_TYPE_P(value) == IS_BOOL) {						\
			ONPHP_UPDATE_PROPERTY_BOOL(							\
				ternary,										\
				"trinity",										\
				zval_is_true(value)								\
			);													\
																\
			goto out;											\
		} else if (Z_TYPE_P(value) == IS_NULL) {				\
			ONPHP_UPDATE_PROPERTY_NULL(ternary, "trinity");		\
																\
			goto out;											\
		}														\
	}															\
																\
	ONPHP_THROW(WrongArgumentException, NULL);

ONPHP_METHOD(Ternary, create)
{
	zval *object, *value;
	
	if (ZEND_NUM_ARGS() == 1) {
		ONPHP_GET_ARGS("z", &value);
	} else {
		ALLOC_INIT_ZVAL(value);
		ZVAL_NULL(value);
	}
	
	ONPHP_MAKE_OBJECT(Ternary, object);
	
	ONPHP_TERNARY_SET_VALUE(object);
	
out:
	if (ZEND_NUM_ARGS() != 1) {
		ZVAL_FREE(value);
	}
	
	if (EG(exception)) {
		zval_ptr_dtor(&object);
		return;
	}
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(Ternary, __construct)
{
	ONPHP_TERNARY_SET_VALUE(getThis());
	
out:
	return;
}

ONPHP_METHOD(Ternary, spawn)
{
	zval *value, *true, *false, *null, *result, *out;
	
	ONPHP_GET_ARGS("zzz|z", &value, &true, &false, &null);
	
	ALLOC_INIT_ZVAL(result);
	
	ONPHP_MAKE_OBJECT(Ternary, out);
	
	if (ZEND_NUM_ARGS() == 3) {
		ALLOC_INIT_ZVAL(null);
		ZVAL_NULL(null);
	}
	
	if (
		is_identical_function(result, value, true TSRMLS_CC)
		&& (zval_is_true(result))
	) {
		ONPHP_UPDATE_PROPERTY_BOOL(out, "trinity", 1);
	} else if (
		is_identical_function(result, value, false TSRMLS_CC)
		&& (zval_is_true(result))
	) {
		ONPHP_UPDATE_PROPERTY_BOOL(out, "trinity", 0);
	} else if (
		(
			is_identical_function(result, value, null TSRMLS_CC)
			&& (zval_is_true(result))
		) || (
			Z_TYPE_P(null) == IS_NULL
		)
	) {
		ONPHP_UPDATE_PROPERTY_NULL(out, "trinity");
	} else {
		ONPHP_THROW_NORET(WrongArgumentException, "failed to spawn Ternary");
	}
	
	ZVAL_FREE(result);
	
	if (ZEND_NUM_ARGS() == 3) {
		ZVAL_FREE(null);
	}
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Ternary, isNull)
{
	RETURN_BOOL(Z_TYPE_P(ONPHP_READ_PROPERTY(getThis(), "trinity")) == IS_NULL);
}

ONPHP_METHOD(Ternary, isTrue)
{
	zval *trinity = ONPHP_READ_PROPERTY(getThis(), "trinity");
	
	RETURN_BOOL(
		(Z_TYPE_P(trinity) == IS_BOOL)
		&& zval_is_true(trinity)
	);
}

ONPHP_METHOD(Ternary, isFalse)
{
	zval *trinity = ONPHP_READ_PROPERTY(getThis(), "trinity");
	
	RETURN_BOOL(
		(Z_TYPE_P(trinity) == IS_BOOL)
		&& !zval_is_true(trinity)
	);
}

ONPHP_METHOD(Ternary, setNull)
{
	ONPHP_UPDATE_PROPERTY_NULL(getThis(), "trinity");
	
	RETURN_THIS;
}

ONPHP_METHOD(Ternary, setTrue)
{
	ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "trinity", 1);
	
	RETURN_THIS;
}

ONPHP_METHOD(Ternary, setFalse)
{
	ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "trinity", 0);
	
	RETURN_THIS;
}

ONPHP_GETTER(Ternary, getValue, trinity);

ONPHP_METHOD(Ternary, setValue)
{
	ONPHP_TERNARY_SET_VALUE(getThis());
	
out:
	RETURN_THIS;
}

#undef ONPHP_TERNARY_SET_VALUE

ONPHP_METHOD(Ternary, decide)
{
	zval
		*true,
		*false,
		*null,
		*trinity = ONPHP_READ_PROPERTY(getThis(), "trinity");
	
	ONPHP_GET_ARGS("zz|z", &true, &false, &null);
	
	if (Z_TYPE_P(trinity) == IS_BOOL) {
		if (zval_is_true(trinity)) {
			RETURN_ZVAL(true, 1, 0);
		} else {
			RETURN_ZVAL(false, 1, 0);
		}
	} else if (Z_TYPE_P(trinity) == IS_NULL) {
		if (ZEND_NUM_ARGS() == 3) {
			RETURN_ZVAL(null, 1, 0);
		} else {
			RETURN_NULL();
		}
	}
	
	ONPHP_THROW(WrongStateException, NULL);
}

ONPHP_METHOD(Ternary, toString)
{
	zval *trinity = ONPHP_READ_PROPERTY(getThis(), "trinity");
	
	if (Z_TYPE_P(trinity) == IS_BOOL) {
		if (zval_is_true(trinity)) {
			RETURN_STRINGL("true", 4, 1);
		} else {
			RETURN_STRINGL("false", 5, 1);
		}
	} else if (Z_TYPE_P(trinity) == IS_NULL) {
		RETURN_STRINGL("null", 4, 1);
	}
	
	ONPHP_THROW(WrongStateException, NULL);
}

static ONPHP_ARGINFO_TWO;
static ONPHP_ARGINFO_THREE;

zend_function_entry onphp_funcs_Ternary[] = {
	ONPHP_ME(Ternary, __construct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(Ternary, create, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Ternary, spawn, arginfo_three, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Ternary, isNull, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Ternary, isTrue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Ternary, isFalse, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Ternary, setNull, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Ternary, setTrue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Ternary, setFalse, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Ternary, getValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Ternary, setValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Ternary, decide, arginfo_two, ZEND_ACC_PUBLIC)
	ONPHP_ME(Ternary, toString, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
