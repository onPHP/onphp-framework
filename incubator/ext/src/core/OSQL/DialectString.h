/* $Id$ */

#ifndef ONPHP_CORE_DIALECT_STRING_H
#define ONPHP_CORE_DIALECT_STRING_H

#include "core/DB/Dialect.h"

extern PHPAPI zend_class_entry *onphp_ce_DialectString;

static
ZEND_BEGIN_ARG_INFO(arginfo_dialect, 0)
	ZEND_ARG_OBJ_INFO(0, Dialect, Dialect, 0)
ZEND_END_ARG_INFO()

extern zend_function_entry onphp_funcs_DialectString[];

#endif /* ONPHP_CORE_DIALECT_STRING_H */
