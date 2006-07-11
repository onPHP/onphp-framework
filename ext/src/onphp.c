/* $Id$ */

#include "php.h"
#include "SAPI.h"
#include "ext/standard/info.h"

#include "onphp.h"
#include "onphp_core.h"
#include "onphp_main.h"

void onphp_empty_object_free_storage(void *object TSRMLS_DC)
{
	onphp_empty_object *empty = (onphp_empty_object *) object;

	zend_object_std_dtor(&empty->std TSRMLS_CC);

	efree(object);
}

zend_object_value onphp_empty_object_spawn(
	zend_class_entry *class_type,
	onphp_empty_object **object TSRMLS_DC
)
{
	zend_object_value objval;
	onphp_empty_object *intern;
	zval *tmp;

	intern = emalloc(sizeof(onphp_empty_object));
	memset(intern, 0, sizeof(onphp_empty_object));

	if (object)
		*object = intern;

	zend_object_std_init(&intern->std, class_type TSRMLS_CC);

	zend_hash_copy(
		intern->std.properties,
		&class_type->default_properties,
		(copy_ctor_func_t) zval_add_ref,
		(void *) &tmp,
		sizeof(zval *)
	);

	objval.handle = zend_objects_store_put(
		intern,
		(zend_objects_store_dtor_t) zend_objects_destroy_object,
		(zend_objects_free_object_storage_t) onphp_empty_object_free_storage,
		NULL TSRMLS_CC
	);
	
	objval.handlers = zend_get_std_object_handlers();

	return objval;
}

zend_object_value onphp_empty_object_new(zend_class_entry *class_type TSRMLS_DC)
{
	return onphp_empty_object_spawn(class_type, NULL TSRMLS_CC);
}

#include "onphp_logo.c"

PHP_MINFO_FUNCTION(onphp)
{
	char pr = 
		!sapi_module.phpinfo_as_text
		&& zend_ini_long("expose_php", sizeof("expose_php"), 0);

	php_info_print_table_start();
	if (pr) {
		PUTS("<tr><td rowspan=\"4\" width=\"202\">");
		PUTS("<a href=\"http://onphp.org/\"><img border=\"0\" src=\"");
		PUTS("?="ONPHP_LOGO_GUID"\" alt=\"onPHP Logo\"");
		PUTS("width=\"202\" height=\"93\" /></a>");
		PUTS("</td></tr>\n");
	}
	php_info_print_table_header(2, "onPHP support", "enabled");
	php_info_print_table_row(2, "Version", ONPHP_VERSION);
	if (pr) {
		PUTS("<tr><td colspan=\"2\">&nbsp;</td></tr>");
	}
	php_info_print_table_end();
}

PHP_MINIT_FUNCTION(onphp)
{
	if (
		!sapi_module.phpinfo_as_text
		&& zend_ini_long("expose_php", sizeof("expose_php"), 0)
	) {
		php_register_info_logo(
			ONPHP_LOGO_GUID,
			"image/png",
			(unsigned char *) onphp_logo,
			sizeof(onphp_logo)
		);
	}

	return
		PHP_MINIT(onphp_core)(INIT_FUNC_ARGS_PASSTHRU)
		& PHP_MINIT(onphp_main)(INIT_FUNC_ARGS_PASSTHRU);
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
