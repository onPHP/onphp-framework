/* $Id$ */

#include "onphp_core.h"

PHPAPI zend_class_entry *onphp_ce_Identifiable;

zend_function_entry onphp_funcs_Identifiable[] = {
	ONPHP_ABSTRACT_ME(Identifiable, getId, NULL)
	ONPHP_ABSTRACT_ME(Identifiable, setId, arginfo_one)
	{NULL, NULL, NULL}
};
