/* $Id$ */

#ifndef ONPHP_CORE_IDENTIFIABLE_OBJECT_H
#define ONPHP_CORE_IDENTIFIABLE_OBJECT_H

#include "onphp_core.h"

extern PHPAPI zend_class_entry *onphp_ce_IdentifiableObject;

typedef struct _onphp_empty_object onphp_identifiable_object;

extern zend_function_entry onphp_funcs_IdentifiableObject[];

#endif /* ONPHP_CORE_IDENTIFIABLE_OBJECT_H */
