/* $Id$ */

#ifndef ONPHP_CORE_H
#define ONPHP_CORE_H

#include "php.h"

#include "onphp.h"

extern PHPAPI zend_class_entry *onphp_ce_Identifiable;
extern PHPAPI zend_class_entry *onphp_ce_IdentifiableObject;

typedef struct _onphp_identifiable_object onphp_identifiable_object;

struct _onphp_identifiable_object {
	zend_object		std;
	zval			*id;
};

PHP_MINIT_FUNCTION(onphp_core);

#endif /* ONPHP_CORE_H */
