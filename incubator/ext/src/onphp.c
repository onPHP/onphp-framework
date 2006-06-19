/* $Id$ */

#include "onphp.h"

#include "onphp_core.h"
#include "core/Base/Identifier.h"
#include "core/Base/Identifiable.h"
#include "core/Base/IdentifiableObject.h"
#include "core/Base/Stringable.h"
#include "core/Base/Singleton.h"
#include "core/Base/Named.h"
#include "core/Base/NamedObject.h"
#include "core/Base/Instantiatable.h"
#include "core/DB/DBValue.h"
#include "core/DB/Dialect.h"
#include "core/OSQL/Castable.h"
#include "core/OSQL/DialectString.h"
#include "core/OSQL/SQLTableName.h"

#define ONPHP_ADD_CLASS(class_name, z_list, sub, allow, ce_flags) \
	spl_add_classes(&onphp_ce_ ## class_name, z_list, sub, allow, ce_flags TSRMLS_CC)

// Exceptions omitted here
#define ONPHP_LIST_CLASSES(z_list, sub, allow, ce_flags) \
	ONPHP_ADD_CLASS(Stringable, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(Singleton, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(Named, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(NamedObject, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(Identifier, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(Identifiable, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(IdentifiableObject, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(Instantiatable, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(DBValue, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(Dialect, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(Castable, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(DialectString, z_list, sub, allow, ce_flags); \
	ONPHP_ADD_CLASS(SQLTableName, z_list, sub, allow, ce_flags);

PHP_FUNCTION(onphp_classes)
{
	array_init(return_value);
	
	ONPHP_LIST_CLASSES(return_value, 0, 0, 0)
}


zend_function_entry onphp_functions[] = {
	PHP_FE(onphp_classes, NULL)
	{NULL, NULL, NULL}
};


PHP_MINFO_FUNCTION(onphp)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "onPHP support", "enabled");
	php_info_print_table_row(2, "Version", ONPHP_VERSION);
	php_info_print_table_end();
}


PHP_MINIT_FUNCTION(onphp)
{
	PHP_MINIT(onphp_core)(INIT_FUNC_ARGS_PASSTHRU);

	return SUCCESS;
}

PHP_RSHUTDOWN_FUNCTION(onphp)
{
	PHP_RSHUTDOWN(onphp_core)(INIT_FUNC_ARGS_PASSTHRU);
}

static zend_module_dep onphp_deps[] = {
	ZEND_MOD_REQUIRED("spl")
	{NULL, NULL, NULL}
};


zend_module_entry onphp_module_entry = {
	STANDARD_MODULE_HEADER_EX, NULL,
	onphp_deps,
	"onPHP",
	onphp_functions,
	PHP_MINIT(onphp),
	NULL,
	NULL,
	PHP_RSHUTDOWN(onphp),
	PHP_MINFO(onphp),
	ONPHP_VERSION,
	STANDARD_MODULE_PROPERTIES
};

ZEND_GET_MODULE(onphp)
