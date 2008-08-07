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

#include "main/Markup/Html/Cdata.h"

ONPHP_CREATOR(Cdata);

ONPHP_SETTER(Cdata, setData, data);

ONPHP_METHOD(Cdata, getData)
{
	zval *strict = ONPHP_READ_PROPERTY(getThis(), "strict");
	zval *data = ONPHP_READ_PROPERTY(getThis(), "data");
	
	if (zval_is_true(strict)) {
		smart_str string = {0};
		
		smart_str_appendl(&string, "<![CDATA[", 8);
		onphp_append_zval_to_smart_string(&string, data);
		smart_str_appendl(&string, "]]>", 3);
		smart_str_0(&string);
		
		RETURN_STRINGL(string.c, string.len, 0);
	} else {
		RETURN_ZVAL(data, 1, 0);
	}
}

ONPHP_GETTER(Cdata, getRawData, data);

ONPHP_METHOD(Cdata, setStrict)
{
	zend_bool strict;
	
	ONPHP_GET_ARGS("b", &strict);
	
	if (strict) {
		ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "strict", 1);
	}
}

ONPHP_GETTER(Cdata, isStrict, strict);

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_Cdata[] = {
	ONPHP_ME(Cdata, create, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Cdata, setData, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Cdata, getData, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Cdata, getRawData, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Cdata, setStrict, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Cdata, isStrict, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
