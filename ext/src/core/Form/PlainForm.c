/***************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                     *
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

#include "core/Form/PlainForm.h"

#include "core/Form/Primitives/BasePrimitive.h"
#include "core/Form/Primitives/ListedPrimitive.h"
#include "core/Form/Primitives/FiltrablePrimitive.h"

ONPHP_METHOD(PlainForm, __construct)
{
	ONPHP_CONSTRUCT_ARRAY(primitives);
}

ONPHP_METHOD(PlainForm, clean)
{
	zval **prm, *primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_FOREACH(primitives, prm) {
		ONPHP_CALL_METHOD_0(*prm, "clean", NULL);
	}
	
	RETURN_THIS;
}

ONPHP_METHOD(PlainForm, exists)
{
	char *name;
	unsigned int length;
	zval *primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_GET_ARGS("s", &name, &length);
	
	RETURN_BOOL(
		ONPHP_ASSOC_ISSET(primitives, name)
	);
}

ONPHP_METHOD(PlainForm, add)
{
	zval
		*name,
		*prm,
		*primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_GET_ARGS("O", &prm, onphp_ce_BasePrimitive);
	
	ONPHP_CALL_METHOD_0(prm, "getname", &name);
	
	if (ONPHP_ASSOC_ISSET(primitives, Z_STRVAL_P(name))) {
		zval_ptr_dtor(&name);
		ONPHP_THROW(WrongArgumentException, "i am already exists!");
	}
	
	ONPHP_ASSOC_SET(primitives, Z_STRVAL_P(name), prm);
	
	zval_ptr_dtor(&name);
	
	RETURN_THIS;
}

ONPHP_METHOD(PlainForm, drop)
{
	char *name;
	unsigned int length;
	zval *primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_GET_ARGS("s", &name, &length);
	
	if (!ONPHP_ASSOC_ISSET(primitives, name)) {
		ONPHP_THROW(
			MissingElementException,
			"can not drop inexistent primitive"
		);
	}
	
	ONPHP_ASSOC_UNSET(primitives, name);
	
	RETURN_THIS;
}

ONPHP_METHOD(PlainForm, get)
{
	char *name;
	unsigned int length;
	zval *primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_GET_ARGS("s", &name, &length);
	
	if (ONPHP_ASSOC_ISSET(primitives, name)) {
		zval *prm;
		
		ONPHP_ASSOC_GET(primitives, name, prm);
		
		RETURN_ZVAL(prm, 1, 0);
	}
	
	ONPHP_THROW(
		MissingElementException,
		"'%s' not found",
		name
	);
}

#define ONPHP_PLAIN_FORM_STRAIGHT_GETTER(method_name, function_name)	\
	ONPHP_PLAIN_FORM_STRAIGHT_PRE_GETTER(method_name)					\
	ONPHP_PLAIN_FORM_STRAIGHT_POST_GETTER(function_name)

#define ONPHP_PLAIN_FORM_STRAIGHT_PRE_GETTER(method_name)				\
ONPHP_METHOD(PlainForm, method_name)									\
{																		\
	zval *name, *prm, *out;												\
																		\
	ONPHP_GET_ARGS("z", &name);											\
																		\
	ONPHP_CALL_METHOD_1(getThis(), "get", &prm, name);

#define ONPHP_PLAIN_FORM_STRAIGHT_POST_GETTER(function_name)			\
	ONPHP_CALL_METHOD_0(prm, function_name, &out);						\
	zval_ptr_dtor(&prm);												\
	RETURN_ZVAL(out, 1, 1);												\
}

ONPHP_PLAIN_FORM_STRAIGHT_GETTER(getValue, "getvalue");
ONPHP_PLAIN_FORM_STRAIGHT_GETTER(getRawValue, "getrawvalue");
ONPHP_PLAIN_FORM_STRAIGHT_GETTER(getActualValue, "getactualvalue");
ONPHP_PLAIN_FORM_STRAIGHT_GETTER(getSafeValue, "getsafevalue");

ONPHP_PLAIN_FORM_STRAIGHT_PRE_GETTER(getChoiceValue) {
	if (!ONPHP_INSTANCEOF(prm, ListedPrimitive)) {
		zval_ptr_dtor(&prm);
		ONPHP_THROW(WrongArgumentException, NULL);
	}
} ONPHP_PLAIN_FORM_STRAIGHT_POST_GETTER("getchoicevalue");

ONPHP_PLAIN_FORM_STRAIGHT_PRE_GETTER(getActualChoiceValue) {
	if (!ONPHP_INSTANCEOF(prm, ListedPrimitive)) {
		zval_ptr_dtor(&prm);
		ONPHP_THROW(WrongArgumentException, NULL);
	}
} ONPHP_PLAIN_FORM_STRAIGHT_POST_GETTER("getactualchoicevalue");

ONPHP_PLAIN_FORM_STRAIGHT_PRE_GETTER(getDisplayValue) {
	if (ONPHP_INSTANCEOF(prm, FiltrablePrimitive)) {
		zval_ptr_dtor(&prm);
		ONPHP_THROW(WrongArgumentException, NULL);
	}
} ONPHP_PLAIN_FORM_STRAIGHT_POST_GETTER("getactualchoicevalue");

#undef ONPHP_PLAIN_FORM_STRAIGHT_GETTER
#undef ONPHP_PLAIN_FORM_STRAIGHT_PRE_GETTER
#undef ONPHP_PLAIN_FORM_STRAIGHT_POST_GETTER

ONPHP_METHOD(PlainForm, getNameList)
{
	zval *out, *primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	zend_call_method_with_1_params(
		NULL,
		NULL,
		NULL,
		"array_keys",
		&out,
		primitives
	);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(PlainForm, getList)
{
	zval *primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	RETURN_ZVAL(primitives, 1, 0);
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_BASE_PRIMITIVE;

zend_function_entry onphp_funcs_PlainForm[] = {
	ONPHP_ME(PlainForm, __construct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(PlainForm, clean, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, exists, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, add, arginfo_base_primitive, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, drop, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, get, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, getValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, getRawValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, getActualValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, getSafeValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, getChoiceValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, getActualChoiceValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, getDisplayValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, getNameList, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(PlainForm, getList, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
