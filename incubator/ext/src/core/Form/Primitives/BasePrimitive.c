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

ONPHP_METHOD(BasePrimitive, __construct)
{
	zval *name;
	
	ONPHP_GET_ARGS("z", &name);
	
	ONPHP_UPDATE_PROPERTY(getThis(), "name", name);
}

ONPHP_GETTER(BasePrimitive, getName, name);

ONPHP_SETTER_START(BasePrimitive, setName, name);
	if (Z_TYPE_P(name) != IS_STRING) {
		ONPHP_THROW(WrongArgumentException, NULL);
	}
ONPHP_SETTER_END(name);

ONPHP_GETTER(BasePrimitive, getValue, value);
ONPHP_SETTER(BasePrimitive, setValue, value);

ONPHP_GETTER(BasePrimitive, getRawValue, raw);
ONPHP_SETTER(BasePrimitive, setRawValue, raw);

ONPHP_GETTER(BasePrimitive, isRequired, required);
ONPHP_SETTER(BasePrimitive, setRequired, required); // !exactMatch

ONPHP_METHOD(BasePrimitive, required)
{
	ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "required", 1);
	
	RETURN_THIS;
}

ONPHP_METHOD(BasePrimitive, optional)
{
	ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "required", 0);
	
	RETURN_THIS;
}

ONPHP_GETTER(BasePrimitive, isImported, imported);

ONPHP_METHOD(BasePrimitive, clean)
{
	ONPHP_UPDATE_PROPERTY_NULL(getThis(), "raw");
	ONPHP_UPDATE_PROPERTY_NULL(getThis(), "value");
	
	ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "imported", 0);
	
	RETURN_THIS;
}

ONPHP_METHOD(BasePrimitive, importValue)
{
	zval *scope, *value, *name, *result;
	
	ONPHP_GET_ARGS("z", &value);
	
	ONPHP_MAKE_ARRAY(scope);
	
	name = ONPHP_READ_PROPERTY(getThis(), "name");
	
	ONPHP_ASSOC_SET(scope, Z_STRVAL_P(name), value);
	
	ONPHP_CALL_METHOD_1(getThis(), "import", &result, scope);
	
	zval_ptr_dtor(&scope);
	
	RETURN_ZVAL(result, 1, 1);
}

ONPHP_METHOD(BasePrimitive, import)
{
	zval *scope, *name, **raw;
	
	ONPHP_GET_ARGS("a", &scope);
	
	if (
		(Z_TYPE_P(scope) == IS_ARRAY)
		&& (zend_hash_num_elements(Z_ARRVAL_P(scope)) > 0)
		&& (name = ONPHP_READ_PROPERTY(getThis(), "name"))
		&& (Z_TYPE_P(name) == IS_STRING)
		&& (
			zend_symtable_find(
				Z_ARRVAL_P(scope),
				Z_STRVAL_P(name),
				Z_STRLEN_P(name) + 1,
				(void **) &raw
			)
			== SUCCESS
		)
		&& !(Z_TYPE_PP(raw) == IS_NULL)
		&& !(
			(Z_TYPE_PP(raw) == IS_STRING)
			&& (Z_STRLEN_PP(raw) == 0)
		)
	) {
		ONPHP_UPDATE_PROPERTY(getThis(), "raw", *raw);
		
		ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "imported", 1);
		
		RETURN_BOOL(1);
	}
	
	ONPHP_CALL_METHOD_0(getThis(), "clean", NULL);
	
	RETURN_NULL();
}

ONPHP_GETTER(BasePrimitive, exportValue, value);

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_BasePrimitive[] = {
	ONPHP_ME(BasePrimitive, __construct, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(BasePrimitive, getName, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, setName, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, getValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, setValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, getRawValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, setRawValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, isRequired, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, setRequired, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, required, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, optional, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, isImported, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, clean, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, importValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, exportValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(BasePrimitive, import, arginfo_one, ZEND_ACC_PROTECTED)
	{NULL, NULL, NULL}
};
