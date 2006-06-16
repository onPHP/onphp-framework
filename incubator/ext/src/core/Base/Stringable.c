/* $Id$ */

#include "onphp_core.h"

PHPAPI zend_class_entry *onphp_ce_Stringable;

zend_function_entry onphp_funcs_Stringable[] = {
	ONPHP_ABSTRACT_ME(Identifiable, toString, NULL)
	{NULL, NULL, NULL}
};
