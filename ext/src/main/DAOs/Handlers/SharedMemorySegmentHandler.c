/* $Id$ */

#include "onphp.h"

#include "main/DAOs/Handlers/SharedMemorySegmentHandler.h"

PHPAPI zend_class_entry *onphp_ce_SharedMemorySegmentHandler;

static ONPHP_ARGINFO_ONE;

PHP_MINIT_FUNCTION(SharedMemorySegmentHandler)
{
	return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(SharedMemorySegmentHandler)
{
	return SUCCESS;
}

ONPHP_METHOD(SharedMemorySegmentHandler, __construct)
{
}

ONPHP_METHOD(SharedMemorySegmentHandler, touch)
{
}

ONPHP_METHOD(SharedMemorySegmentHandler, unlink)
{
}

ONPHP_METHOD(SharedMemorySegmentHandler, ping)
{
}

ONPHP_METHOD(SharedMemorySegmentHandler, drop)
{
}


zend_function_entry onphp_funcs_SharedMemorySegmentHandler[] = {
	ONPHP_ME(SharedMemorySegmentHandler, __construct,	arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(SharedMemorySegmentHandler, touch,			arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(SharedMemorySegmentHandler, unlink,		arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(SharedMemorySegmentHandler, ping,			arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(SharedMemorySegmentHandler, drop,			NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
