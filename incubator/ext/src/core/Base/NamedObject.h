/* $Id$ */

#ifndef ONPHP_CORE_NAMED_OBJECT_H
#define ONPHP_CORE_NAMED_OBJECT_H

#include "onphp_core.h"

extern PHPAPI zend_class_entry *onphp_ce_NamedObject;

typedef struct _onphp_empty_object onphp_named_object;

extern zend_function_entry onphp_funcs_NamedObject[];

static
ZEND_BEGIN_ARG_INFO(arginfo_two_named_objects, 0)
	ZEND_ARG_INFO(0, named)
	ZEND_ARG_INFO(0, named)
ZEND_END_ARG_INFO()

#endif /* ONPHP_CORE_NAMED_OBJECT_H */
