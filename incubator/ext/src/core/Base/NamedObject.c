/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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

#include "core/Base/NamedObject.h"

ONPHP_GETTER(NamedObject, getName, name);
ONPHP_SETTER(NamedObject, setName, name);

ONPHP_METHOD(NamedObject, toString)
{
	smart_str string = {0};
	
	smart_str_appendc(&string, '[');
	onphp_append_zval_to_smart_string(
		&string,
		ONPHP_READ_PROPERTY(getThis(), "id")
	);
	smart_str_appendl(&string, "] ", 2);
	onphp_append_zval_to_smart_string(
		&string,
		ONPHP_READ_PROPERTY(getThis(), "name")
	);
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(NamedObject, compareNames)
{
	zval *first, *second;
	zval *left, *right;
	int result;
	
	ONPHP_GET_ARGS(
		"OO",
		&first, onphp_ce_NamedObject,
		&second, onphp_ce_NamedObject
	);
	
	ONPHP_CALL_METHOD_0(first, "getname", &left);
	ONPHP_CALL_METHOD_0(second, "getname", &right);
	
	result = strcasecmp(Z_STRVAL_P(left), Z_STRVAL_P(right));
	
	ZVAL_FREE(left);
	ZVAL_FREE(right);
	
	RETURN_LONG(result);
}

static ONPHP_ARGINFO_ONE;

static
ZEND_BEGIN_ARG_INFO(arginfo_two_named_objects, 0)
	ZEND_ARG_OBJ_INFO(0, Named, Named, 0)
	ZEND_ARG_OBJ_INFO(0, Named, Named, 0)
ZEND_END_ARG_INFO();

zend_function_entry onphp_funcs_NamedObject[] = {
	ONPHP_ME(NamedObject, getName,		NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(NamedObject, setName,		arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(NamedObject, compareNames,	arginfo_two_named_objects, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(NamedObject, toString,		NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
