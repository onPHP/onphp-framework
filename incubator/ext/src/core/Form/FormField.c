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

#include "core/Form/Form.h"
#include "core/Form/FormField.h"

ONPHP_METHOD(FormField, create)
{
	char *name;
	unsigned int length;
	zval *object;
	
	ONPHP_GET_ARGS("s", &name, &length);
	
	ONPHP_MAKE_OBJECT(FormField, object);
	
	ONPHP_UPDATE_PROPERTY_STRINGL(getThis(), "primitiveName", name, length);
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(FormField, __construct)
{
	char *name;
	unsigned int length;
	
	ONPHP_GET_ARGS("s", &name, &length);
	
	ONPHP_UPDATE_PROPERTY_STRINGL(getThis(), "primitiveName", name, length);
}

ONPHP_GETTER(FormField, getName, primitiveName);

ONPHP_METHOD(FormField, toValue)
{
	zval *form, *name, *out;
	
	ONPHP_GET_ARGS("O", &form, onphp_ce_Form);
	
	name = ONPHP_READ_PROPERTY(getThis(), "primitiveName");
	
	ONPHP_CALL_METHOD_1(form, "getvalue", &out, name);
	
	RETURN_ZVAL(out, 1, 1);
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_FORM;

zend_function_entry onphp_funcs_FormField[] = {
	ONPHP_ME(FormField, __construct, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(FormField, create, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(FormField, getName, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FormField, toValue, arginfo_form, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
