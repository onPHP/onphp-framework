/* $Id$ */

#include "onphp.h"
#include "onphp_core.h"

PHPAPI zend_class_entry *onphp_ce_Dialect;

static
ZEND_BEGIN_ARG_INFO(arginfo_autoincrementize, 0)
	ZEND_ARG_OBJ_INFO(0, DBColumn, DBColumn, 0)
	ZEND_ARG_INFO(1, prepend)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO(arginfo_one_ref, 0)
	ZEND_ARG_INFO(1, value)
ZEND_END_ARG_INFO();

ONPHP_METHOD(Dialect, quoteValue)
{
	zval *value;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	// don't know, how to replicate original voodoo
	if (Z_TYPE_P(value) == IS_LONG) {
		RETURN_LONG(Z_LVAL_P(value));
	} else {
		char *slashed;
		int length = 0;
		
		slashed = php_addslashes((char *) Z_STRVAL_P(value), Z_STRLEN_P(value), &length, 0 TSRMLS_CC);
		
		length += 2;
		
		slashed = erealloc(slashed, length);
		
		slashed[length] = 0;
		
		memmove(slashed + 1, slashed, length - 2);
		
		slashed[0] = 39;
		slashed[length - 1] = 39;
		
		RETURN_STRINGL(slashed, length, 0);
	}
}

zend_function_entry onphp_funcs_Dialect[] = {
	ONPHP_ABSTRACT_ME(Dialect, autoincrementize, arginfo_autoincrementize, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, quoteValue, arginfo_one_ref, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	{NULL, NULL, NULL}
};
