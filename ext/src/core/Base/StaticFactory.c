/* $Id$ */

#include "onphp.h"

#include "core/Base/StaticFactory.h"

PHPAPI zend_class_entry *onphp_ce_StaticFactory;

ONPHP_METHOD(StaticFactory, __construct)
{
	// doh
}

zend_function_entry onphp_funcs_StaticFactory[] = {
	ONPHP_ME(StaticFactory, __construct, NULL, ZEND_ACC_FINAL | ZEND_ACC_PRIVATE)
	{NULL, NULL, NULL}
};
