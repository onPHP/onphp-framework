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

#include "core/Exceptions.h"

#include "core/DB/ImaginaryDialect.h"

ONPHP_METHOD(QueryIdentification, getId)
{
	zval *out, *hashed;
	
	ONPHP_CALL_METHOD_0(getThis(), "tostring", &out);
	
	zend_call_method_with_1_params(
		NULL,
		NULL,
		NULL,
		"sha1",
		&hashed,
		out
	);
	
	ZVAL_FREE(out);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(hashed, 1, 1);
}

ONPHP_METHOD(QueryIdentification, toString)
{
	zval *out, *imdi;
	
	ONPHP_CALL_STATIC_0(ImaginaryDialect, "me", &imdi);
	
	ONPHP_CALL_METHOD_1_NORET(getThis(), "todialectstring", &out, imdi);
	
	zval_ptr_dtor(&imdi);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(QueryIdentification, setId)
{
	ONPHP_THROW(UnsupportedMethodException, NULL);
}

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_QueryIdentification[] = {
	ONPHP_ME(QueryIdentification, getId, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(QueryIdentification, setId, arginfo_one, ZEND_ACC_FINAL | ZEND_ACC_PUBLIC)
	ONPHP_ME(QueryIdentification, toString, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
