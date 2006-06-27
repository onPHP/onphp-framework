/* $Id$ */

#ifndef ONPHP_CORE_SINGLETON_H
#define ONPHP_CORE_SINGLETON_H

extern PHPAPI zend_class_entry *onphp_ce_Singleton;

extern zend_function_entry onphp_funcs_Singleton[];

extern PHP_RSHUTDOWN_FUNCTION(Singleton);

#endif /* ONPHP_CORE_SINGLETON_H */
