/* $Id$ */

#include "onphp_core.h"

PHPAPI zend_class_entry *onphp_ce_Castable;

ONPHP_METHOD(Castable, castTo)
{
	zval *this = getThis(), *cast;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &cast) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(this, "cast", cast);

	RETURN_ZVAL(this, 1, 0);
}

zend_function_entry onphp_funcs_Castable[] = {
	ONPHP_ME(Castable, castTo, arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
