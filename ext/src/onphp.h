/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#ifndef ONPHP_H
#define ONPHP_H

#include "php.h"
#include "zend_interfaces.h"

#define ONPHP_VERSION "0.10.10"
#define ONPHP_MODULE_NAME "onPHP"

#define ZVAL_FREE(z) zval_dtor(z); FREE_ZVAL(z);

#define ONPHP_ME(class_name, function_name, arg_info, flags) \
	PHP_ME(onphp_ ## class_name, function_name, arg_info, flags)

#define ONPHP_ABSTRACT_ME(class_name, function_name, arg_info, flags) \
	ZEND_FENTRY(function_name, NULL, arg_info, flags|ZEND_ACC_ABSTRACT)

#define REGISTER_ONPHP_INTERFACE(class_name) \
	spl_register_interface(&onphp_ce_ ## class_name, # class_name, onphp_funcs_ ## class_name TSRMLS_CC);

#define REGISTER_ONPHP_IMPLEMENTS(class_name, interface_name) \
	zend_class_implements(onphp_ce_ ## class_name TSRMLS_CC, 1, onphp_ce_ ## interface_name);

#define REGISTER_ONPHP_STD_CLASS(class_name, obj_ctor) \
	spl_register_std_class(&onphp_ce_ ## class_name, # class_name, obj_ctor, NULL TSRMLS_CC);

#define REGISTER_ONPHP_STD_CLASS_EX(class_name) \
	spl_register_std_class(&onphp_ce_ ## class_name, # class_name, onphp_empty_object_new, onphp_funcs_ ## class_name TSRMLS_CC);

#define REGISTER_ONPHP_SUB_CLASS_EX(class_name, parent_class_name) \
	spl_register_sub_class(&onphp_ce_ ## class_name, onphp_ce_ ## parent_class_name, # class_name, onphp_empty_object_new, onphp_funcs_ ## class_name TSRMLS_CC);

#define REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(class_name, parent_class_name, obj_ctor, funcs) \
	spl_register_sub_class(&onphp_ce_ ## class_name, onphp_ce_ ## parent_class_name, # class_name, obj_ctor, funcs TSRMLS_CC);

#define REGISTER_ONPHP_PROPERTY(class_name, prop_name, prop_flags) \
	zend_declare_property_null(onphp_ce_ ## class_name, prop_name, strlen(prop_name), prop_flags TSRMLS_CC);

#define REGISTER_ONPHP_PROPERTY_BOOL(class_name, prop_name, bool, prop_flags) \
	zend_declare_property_bool(onphp_ce_ ## class_name, prop_name, strlen(prop_name), bool, prop_flags TSRMLS_CC);

#define REGISTER_ONPHP_CLASS_CONST_LONG(class_name, const_name, value) \
	zend_declare_class_constant_long(onphp_ce_ ## class_name, const_name, strlen(const_name), (long) value TSRMLS_CC);

#define ONPHP_READ_PROPERTY(class, property) \
	zend_read_property(Z_OBJCE_P(class), class, property, strlen(property), 1 TSRMLS_CC)

#define ONPHP_UPDATE_PROPERTY(class, property, value) \
	zend_update_property(Z_OBJCE_P(class), class, property, strlen(property), value TSRMLS_CC)

#define ONPHP_UPDATE_PROPERTY_BOOL(class, property, value) \
	zend_update_property_bool(Z_OBJCE_P(class), class, property, strlen(property), value TSRMLS_CC)

#define ONPHP_UPDATE_PROPERTY_LONG(class, property, value) \
	zend_update_property_long(Z_OBJCE_P(class), class, property, strlen(property), value TSRMLS_CC)

#define ONPHP_METHOD(class_name, function_name) \
	PHP_METHOD(onphp_ ## class_name, function_name)

#define ONPHP_ARGINFO_ONE \
	ZEND_BEGIN_ARG_INFO(arginfo_one, 0) \
		ZEND_ARG_INFO(0, first) \
	ZEND_END_ARG_INFO()

#define ONPHP_ARGINFO_ONE_REF \
	ZEND_BEGIN_ARG_INFO(arginfo_one_ref, 0) \
		ZEND_ARG_INFO(1, value) \
	ZEND_END_ARG_INFO() \

#define ONPHP_ARGINFO_TWO \
	ZEND_BEGIN_ARG_INFO(arginfo_two, 0) \
		ZEND_ARG_INFO(0, first) \
		ZEND_ARG_INFO(0, second) \
	ZEND_END_ARG_INFO()

#define ONPHP_ARGINFO_THREE \
	ZEND_BEGIN_ARG_INFO(arginfo_three, 0) \
		ZEND_ARG_INFO(0, first) \
		ZEND_ARG_INFO(0, second) \
		ZEND_ARG_INFO(0, third) \
	ZEND_END_ARG_INFO()

#define onphp_empty_object zend_object

extern void onphp_empty_object_free_storage(void *object TSRMLS_DC);
extern zend_object_value onphp_empty_object_spawn(
	zend_class_entry *class_type,
	onphp_empty_object **object TSRMLS_DC
);
extern zend_object_value onphp_empty_object_new(
	zend_class_entry *class_type TSRMLS_DC
);

#endif /* ONPHP_H */
