/* $Id$ */

#include "onphp_core.h"

#include "core/Base/Stringable.h"

PHPAPI zend_class_entry *onphp_ce_Stringable;

zend_function_entry onphp_funcs_Stringable[] = {
	ONPHP_ABSTRACT_ME(Identifiable, toString, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
