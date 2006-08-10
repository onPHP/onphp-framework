/* $Id$ */

#include "onphp.h"
#include "onphp_core.h"

#include "zend_globals.h"
#include "zend_exceptions.h"

#include "core/Exceptions.h"
#include "core/DB/ImaginaryDialect.h"
#include "core/OSQL/QueryIdentification.h"

PHPAPI zend_class_entry *onphp_ce_QueryIdentification;

ONPHP_METHOD(QueryIdentification, getId)
{
	zval *out, *hashed;
	
	zend_call_method_with_0_params(
		&getThis(),
		Z_OBJCE_P(getThis()),
		NULL,
		"tostring",
		&out
	);
	
	if (EG(exception)) {
		return;
	}
	
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
	
	zend_call_method_with_0_params(
		NULL,
		onphp_ce_ImaginaryDialect,
		NULL,
		"me",
		&imdi
	);
	
	if (EG(exception)) {
		return;
	}
	
	zend_call_method_with_1_params(
		&getThis(),
		Z_OBJCE_P(getThis()),
		NULL,
		"todialectstring",
		&out,
		imdi
	);
	
	ZVAL_FREE(imdi);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(QueryIdentification, setId)
{
	zend_throw_exception_ex(
		onphp_ce_UnsupportedMethodException,
		0 TSRMLS_CC,
		NULL
	);
}

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_QueryIdentification[] = {
	ONPHP_ME(QueryIdentification, getId, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(QueryIdentification, setId, arginfo_one, ZEND_ACC_FINAL | ZEND_ACC_PUBLIC)
	ONPHP_ME(QueryIdentification, toString, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
