/* $Id$ */

#ifndef ONPHP_CORE_SINGLETON_H
#define ONPHP_CORE_SINGLETON_H

PHPAPI zend_class_entry *onphp_ce_Singleton;

extern zend_function_entry onphp_funcs_Singleton[];

PHP_MINIT_FUNCTION(Singleton);
PHP_RINIT_FUNCTION(Singleton);
PHP_RSHUTDOWN_FUNCTION(Singleton);

#endif /* ONPHP_CORE_SINGLETON_H */
