/* $Id$ */

#include "onphp_core.h"

PHPAPI zend_class_entry *onphp_ce_Named;

zend_function_entry onphp_funcs_Named[] = {
	ONPHP_ABSTRACT_ME(Identifiable, getName, NULL)
	ONPHP_ABSTRACT_ME(Identifiable, setName, arginfo_one)
	{NULL, NULL, NULL}
};
