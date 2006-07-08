/* $Id$ */

#include "onphp_core.h"

PHPAPI zend_class_entry *onphp_ce_Castable;

ONPHP_METHOD(Castable, castTo)
{
	zval *cast;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &cast) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(getThis(), "cast", cast);

	RETURN_ZVAL(getThis(), 1, 0);
}

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_Castable[] = {
	ONPHP_ME(Castable, castTo, arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
