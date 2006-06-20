/* $Id$ */

#include "onphp_core.h"

#include "core/DB/Dialect.h"

PHPAPI zend_class_entry *onphp_ce_DialectString;

static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_DialectString[] = {
	ONPHP_ABSTRACT_ME(DialectString, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
