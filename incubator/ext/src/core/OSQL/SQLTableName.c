/* $Id$ */

#include "onphp_core.h"

PHPAPI zend_class_entry *onphp_ce_SQLTableName;

zend_function_entry onphp_funcs_SQLTableName[] = {
	ONPHP_ABSTRACT_ME(SQLTableName, getTable, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
