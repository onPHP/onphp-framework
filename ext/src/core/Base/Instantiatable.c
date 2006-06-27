/* $Id$ */

#include "onphp_core.h"

PHPAPI zend_class_entry *onphp_ce_Instantiatable;

zend_function_entry onphp_funcs_Instantiatable[] = {
	ONPHP_ABSTRACT_ME(Instantiatable, me, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	{NULL, NULL, NULL}
};
