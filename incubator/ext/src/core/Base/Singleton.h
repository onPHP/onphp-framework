/* $Id$ */

#ifndef ONPHP_CORE_SINGLETON_H
#define ONPHP_CORE_SINGLETON_H

extern PHPAPI zend_class_entry *onphp_ce_Singleton;
extern PHPAPI zend_class_entry *onphp_ce_SingletonInstance;

extern zend_function_entry onphp_funcs_Singleton[];
extern zend_function_entry onphp_funcs_SingletonInstance[];

static
ZEND_BEGIN_ARG_INFO(arginfo_magic_call, 0)
	ZEND_ARG_INFO(0, name)
	ZEND_ARG_INFO(0, args)
ZEND_END_ARG_INFO()

extern PHP_RSHUTDOWN_FUNCTION(Singleton);

#endif /* ONPHP_CORE_SINGLETON_H */
