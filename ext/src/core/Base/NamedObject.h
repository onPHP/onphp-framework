/* $Id$ */

#ifndef ONPHP_CORE_NAMED_OBJECT_H
#define ONPHP_CORE_NAMED_OBJECT_H

#include "onphp_core.h"

extern PHPAPI zend_class_entry *onphp_ce_NamedObject;

typedef struct _onphp_empty_object onphp_named_object;

extern zend_function_entry onphp_funcs_NamedObject[];

#endif /* ONPHP_CORE_NAMED_OBJECT_H */
