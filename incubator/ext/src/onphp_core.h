/* $Id$ */

#ifndef ONPHP_CORE_H
#define ONPHP_CORE_H

#include "php.h"

#include "onphp.h"

extern zend_object_value onphp_empty_object_new(zend_class_entry *class_type TSRMLS_DC);

typedef struct _onphp_empty_object onphp_empty_object;

struct _onphp_empty_object {
	zend_object std;
};

static
ZEND_BEGIN_ARG_INFO(arginfo_one, 0)
	ZEND_ARG_INFO(0, first)
ZEND_END_ARG_INFO()

static
ZEND_BEGIN_ARG_INFO(arginfo_two, 0)
	ZEND_ARG_INFO(0, first)
	ZEND_ARG_INFO(0, second)
ZEND_END_ARG_INFO()

extern PHP_MINIT_FUNCTION(onphp_core);
extern PHP_RSHUTDOWN_FUNCTION(onphp_core);

#endif /* ONPHP_CORE_H */
