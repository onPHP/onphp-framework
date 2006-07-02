/* $Id$ */

#ifndef ONPHP_CORE_DIALECT_H
#define ONPHP_CORE_DIALECT_H

#include "onphp_core.h"

#define ONPHP_ARGINFO_DIALECT \
	ZEND_BEGIN_ARG_INFO(arginfo_dialect, 0) \
		ZEND_ARG_OBJ_INFO(0, dialect, Dialect, 0) \
	ZEND_END_ARG_INFO()

PHPAPI zend_class_entry *onphp_ce_Dialect;

extern zend_function_entry onphp_funcs_Dialect[];

#endif /* ONPHP_CORE_DIALECT_H */
