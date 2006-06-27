/* $Id$ */

#include "php.h"
#include "ext/standard/info.h"

#include "onphp.h"
#include "onphp_core.h"

PHP_MINFO_FUNCTION(onphp)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "onPHP support", "enabled");
	php_info_print_table_row(2, "Version", ONPHP_VERSION);
	php_info_print_table_end();
}


PHP_MINIT_FUNCTION(onphp)
{
	return PHP_MINIT(onphp_core)(INIT_FUNC_ARGS_PASSTHRU);
}

PHP_RINIT_FUNCTION(onphp)
{
	return PHP_RINIT(onphp_core)(INIT_FUNC_ARGS_PASSTHRU);
}

PHP_RSHUTDOWN_FUNCTION(onphp)
{
	return PHP_RSHUTDOWN(onphp_core)(INIT_FUNC_ARGS_PASSTHRU);
}

static zend_module_dep onphp_deps[] = {
	ZEND_MOD_REQUIRED("spl")
	{NULL, NULL, NULL}
};


zend_module_entry onphp_module_entry = {
	STANDARD_MODULE_HEADER_EX, NULL,
	onphp_deps,
	ONPHP_MODULE_NAME,
	NULL,
	PHP_MINIT(onphp),
	NULL,
	PHP_RINIT(onphp),
	PHP_RSHUTDOWN(onphp),
	PHP_MINFO(onphp),
	ONPHP_VERSION,
	STANDARD_MODULE_PROPERTIES
};

ZEND_GET_MODULE(onphp);
