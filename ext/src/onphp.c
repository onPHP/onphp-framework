/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "SAPI.h"
#include "zend_ini.h" // zend_ini_long
#include "php_logos.h" // php_register_info_logo
#include "ext/standard/info.h"

#include "onphp.h"
#include "onphp_core.h"
#include "onphp_main.h"

void onphp_empty_object_free_storage(void *object TSRMLS_DC)
{
	onphp_empty_object *empty = (onphp_empty_object *) object;

	zend_object_std_dtor(empty TSRMLS_CC);

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

	zend_object_std_init(intern, class_type TSRMLS_CC);

	zend_hash_copy(
		intern->properties,
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

static unsigned char onphp_enable_logo = 0;

PHP_MINFO_FUNCTION(onphp)
{
	php_info_print_table_start();
	if (onphp_enable_logo) {
		PUTS("<tr><td rowspan=\"6\" width=\"202\" style=\"vertical-align: middle;\">");
		PUTS("<a href=\"http://onphp.org/\"><img border=\"0\" src=\"");
		if (SG(request_info).request_uri) {
			char *elem_esc = php_info_html_esc(SG(request_info).request_uri TSRMLS_CC);
			PUTS(elem_esc);
			efree(elem_esc);
		}
		PUTS("?="ONPHP_LOGO_GUID"\" alt=\"onPHP Logo\" ");
		PUTS("width=\"202\" height=\"93\" /></a>");
		PUTS("</td></tr>\n");
	}
	php_info_print_table_header(2, "onPHP support", "enabled");
	php_info_print_table_row(2, "Version", ONPHP_VERSION);
	php_info_print_table_row(2, "Exceptions", ONPHP_EXCEPTIONS_LIST);
	php_info_print_table_row(2, "Interfaces", ONPHP_INTERFACES_LIST);
	php_info_print_table_row(2, "Classes", ONPHP_CLASSES_LIST);
	php_info_print_table_end();
}

PHP_MINIT_FUNCTION(onphp)
{
	onphp_enable_logo = (
		!sapi_module.phpinfo_as_text
		&& zend_ini_long("expose_php", sizeof("expose_php"), 0)
		&& !EG(in_execution)
	);
	
	if (onphp_enable_logo) {
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

PHP_MSHUTDOWN_FUNCTION(onphp)
{
	php_unregister_info_logo(ONPHP_LOGO_GUID);
	
	return SUCCESS;
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
	PHP_MSHUTDOWN(onphp),
	PHP_RINIT(onphp),
	PHP_RSHUTDOWN(onphp),
	PHP_MINFO(onphp),
	ONPHP_VERSION,
	STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_ONPHP
ZEND_GET_MODULE(onphp);
#endif
