/* $Id$ */

#include "onphp.h"
#include "onphp_core.h"

PHPAPI zend_class_entry *onphp_ce_Dialect;

static
ZEND_BEGIN_ARG_INFO(arginfo_autoincrementize, 0)
	ZEND_ARG_OBJ_INFO(0, DBColumn, DBColumn, 0)
	ZEND_ARG_INFO(1, prepend)
ZEND_END_ARG_INFO();

zend_function_entry onphp_funcs_Dialect[] = {
	ONPHP_ABSTRACT_ME(Dialect, autoincrementize, arginfo_autoincrementize, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	{NULL, NULL, NULL}
};
