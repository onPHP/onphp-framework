/* $Id$ */

#include "onphp_core.h"

#include "core/DB/Dialect.h"

PHPAPI zend_class_entry *onphp_ce_DialectString;

static
ZEND_BEGIN_ARG_INFO(arginfo_dialect, 0)
	ZEND_ARG_OBJ_INFO(0, Dialect, Dialect, 0)
ZEND_END_ARG_INFO();

zend_function_entry onphp_funcs_DialectString[] = {
	ONPHP_ABSTRACT_ME(DialectString, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
