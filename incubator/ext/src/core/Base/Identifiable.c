/* $Id$ */

#include "onphp_core.h"

#include "core/Base/Identifiable.h"

PHPAPI zend_class_entry *onphp_ce_Identifiable;

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_Identifiable[] = {
	ONPHP_ABSTRACT_ME(Identifiable, getId, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ABSTRACT_ME(Identifiable, setId, arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
