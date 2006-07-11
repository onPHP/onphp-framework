/* $Id$ */

#include "onphp_core.h"

#include "core/Base/Prototyped.h"

PHPAPI zend_class_entry *onphp_ce_Prototyped;

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_Prototyped[] = {
	ONPHP_ABSTRACT_ME(Prototyped, proto, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	{NULL, NULL, NULL}
};
