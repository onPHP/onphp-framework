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
#include "Zend/zend_globals.h" // executor_globals

#define ONPHP_VERSION "1.0.7.99"
#define ONPHP_MODULE_NAME "onPHP"

/// dumb copy of zend_call_method
ZEND_API zval* onphp_call_method(zval **object_pp, zend_class_entry *obj_ce, zend_function **fn_proxy, char *function_name, int function_name_len, zval **retval_ptr_ptr, int param_count, zval* arg1, zval* arg2, zval* arg3 TSRMLS_DC);

#define ZVAL_FREE(z) zval_dtor(z); FREE_ZVAL(z);

#define ONPHP_CHECK_EMPTY(value) !((						\
		Z_TYPE_P(value) == IS_NULL							\
	) || (													\
		(Z_TYPE_P(value) == IS_LONG)						\
		&& (Z_LVAL_P(value) == 0)							\
	) || (													\
		(Z_TYPE_P(value) == IS_BOOL)						\
		&& (!zval_is_true(value))							\
	) || (													\
		(Z_TYPE_P(value) == IS_STRING)						\
		&& (												\
			(Z_STRLEN_P(value) < 1)							\
			|| (strcmp(Z_STRVAL_P(value), "0") == 0)		\
		)													\
	) || (													\
		(Z_TYPE_P(value) == IS_ARRAY)						\
		&& (zend_hash_num_elements(Z_ARRVAL_P(value)) < 1)	\
	))

#define ONPHP_MAKE_ARRAY(array)	\
	ALLOC_ZVAL(array);			\
	array_init(array);			\
	INIT_PZVAL(array);			\

#define ONPHP_CONSTRUCT_ARRAY(name) {				\
	zval *name;										\
	ONPHP_MAKE_ARRAY(name);							\
	ONPHP_UPDATE_PROPERTY(getThis(), # name, name);	\
	zval_ptr_dtor(&name);							\
}

#define ONPHP_INSTANCEOF(object, class_name)		\
	(												\
		(Z_TYPE_P(object) == IS_OBJECT)				\
		&& instanceof_function(						\
			Z_OBJCE_P(object),						\
			onphp_ce_ ## class_name TSRMLS_CC		\
		)											\
	)

#define ONPHP_FIND_FOREIGN_CLASS(class_name, cep)			\
	if (													\
		zend_lookup_class(									\
			class_name,										\
			strlen(class_name),								\
			&cep TSRMLS_CC									\
		)													\
		== FAILURE											\
	) {														\
		ONPHP_THROW(ClassNotFoundException, class_name);	\
	}

#define ONPHP_GET_ARGS(type_spec, ...)	\
	if (								\
		zend_parse_parameters(			\
			ZEND_NUM_ARGS() TSRMLS_CC,	\
			type_spec,					\
			__VA_ARGS__					\
		)								\
		== FAILURE						\
	) {									\
		WRONG_PARAM_COUNT;				\
	}

#define ONPHP_CALL_AND_RETURN(call, ...)	\
	call(__VA_ARGS__);						\
	if (EG(exception)) return

#define ONPHP_CALL_METHOD_0_NORET(object, method_name, out) \
	zend_call_method_with_0_params(&object, Z_OBJCE_P(object), NULL, method_name, out)

#define ONPHP_CALL_METHOD_0(object, method_name, out) \
	ONPHP_CALL_AND_RETURN(ONPHP_CALL_METHOD_0_NORET, object, method_name, out)

#define ONPHP_CALL_METHOD_1_NORET(object, method_name, out, first_argument) \
	zend_call_method_with_1_params(&object, Z_OBJCE_P(object), NULL, method_name, out, first_argument)

#define ONPHP_CALL_METHOD_1(object, method_name, out, first_argument) \
	ONPHP_CALL_AND_RETURN(ONPHP_CALL_METHOD_1_NORET, object, method_name, out, first_argument)

#define ONPHP_CALL_METHOD_2_NORET(object, method_name, out, first_argument, second_argument) \
	zend_call_method_with_2_params(&object, Z_OBJCE_P(object), NULL, method_name, out, first_argument, second_argument)

#define ONPHP_CALL_METHOD_2(object, method_name, out, first_argument, second_argument) \
	ONPHP_CALL_AND_RETURN(ONPHP_CALL_METHOD_2_NORET, object, method_name, out, first_argument, second_argument)

#define ONPHP_CALL_METHOD_3_NORET(object, method_name, out, first_argument, second_argument, third_argument) \
	onphp_call_method(&object, Z_OBJCE_P(object), NULL, method_name, sizeof(method_name) - 1, out, 3, first_argument, second_argument, third_argument TSRMLS_CC)

#define ONPHP_CALL_METHOD_3(object, method_name, out, first_argument, second_argument, third_argument) \
	ONPHP_CALL_AND_RETURN(ONPHP_CALL_METHOD_3_NORET, object, method_name, out, first_argument, second_argument, third_argument)

#define ONPHP_CALL_PARENT_0_NORET(object, method_name, out)					\
	zend_call_method_with_0_params(											\
		&object,															\
		Z_OBJCE_P(object)->parent,											\
		NULL,																\
		method_name,														\
		out																	\
	);

#define ONPHP_CALL_PARENT_0(object, method_name, out) \
	ONPHP_CALL_AND_RETURN(ONPHP_CALL_PARENT_0_NORET, object, method_name, out)

#define ONPHP_CALL_PARENT_1_NORET(object, method_name, out, first_argument)	\
	zend_call_method_with_1_params(											\
		&object,															\
		Z_OBJCE_P(object)->parent,											\
		NULL,																\
		method_name,														\
		out,																\
		first_argument														\
	);

#define ONPHP_CALL_PARENT_1(object, method_name, out, first_argument) \
	ONPHP_CALL_AND_RETURN(ONPHP_CALL_PARENT_1_NORET, object, method_name, out, first_argument)

#define ONPHP_CALL_STATIC_0_NORET(class, method_name, out) \
	zend_call_method_with_0_params(NULL, onphp_ce_ ## class, NULL, method_name, out)

#define ONPHP_CALL_STATIC_0(class, method_name, out) \
	ONPHP_CALL_AND_RETURN(ONPHP_CALL_STATIC_0_NORET, class, method_name, out)

#define ONPHP_CALL_STATIC_1_NORET(class, method_name, out, first_argument) \
	zend_call_method_with_1_params(NULL, onphp_ce_ ## class, NULL, method_name, out, first_argument)

#define ONPHP_CALL_STATIC_1(class, method_name, out, first_argument) \
	ONPHP_CALL_AND_RETURN(ONPHP_CALL_STATIC_1_NORET, class, method_name, out, first_argument)

#define ONPHP_CALL_STATIC_2_NORET(class, method_name, out, first_argument, second_argument) \
	zend_call_method_with_2_params(NULL, onphp_ce_ ## class, NULL, method_name, out, first_argument, second_argument)

#define ONPHP_CALL_STATIC_2(class, method_name, out, first_argument) \
	ONPHP_CALL_AND_RETURN(ONPHP_CALL_STATIC_2_NORET, class, method_name, out, first_argument, second_argument)

#define ONPHP_ME(class_name, function_name, arg_info, flags) \
	PHP_ME(onphp_ ## class_name, function_name, arg_info, flags)

#define ONPHP_ABSTRACT_ME(class_name, function_name, arg_info, flags) \
	ZEND_FENTRY(function_name, NULL, arg_info, flags|ZEND_ACC_ABSTRACT)

#define REGISTER_ONPHP_INTERFACE(class_name) \
	spl_register_interface(&onphp_ce_ ## class_name, # class_name, onphp_funcs_ ## class_name TSRMLS_CC);

#define REGISTER_ONPHP_IMPLEMENTS(class_name, interface_name) \
	zend_class_implements(onphp_ce_ ## class_name TSRMLS_CC, 1, onphp_ce_ ## interface_name);

#define REGISTER_ONPHP_STD_CLASS(class_name) \
	spl_register_std_class(&onphp_ce_ ## class_name, # class_name, onphp_empty_object_new, onphp_funcs_ ## class_name TSRMLS_CC);

#define REGISTER_ONPHP_SUB_CLASS(class_name, parent_class_name) \
	spl_register_sub_class(&onphp_ce_ ## class_name, onphp_ce_ ## parent_class_name, # class_name, onphp_empty_object_new, onphp_funcs_ ## class_name TSRMLS_CC);

#define REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(class_name, parent_class_name, obj_ctor, funcs) \
	spl_register_sub_class(&onphp_ce_ ## class_name, onphp_ce_ ## parent_class_name, # class_name, obj_ctor, funcs TSRMLS_CC);

#define REGISTER_ONPHP_PROPERTY(class_name, prop_name, prop_flags) \
	zend_declare_property_null(onphp_ce_ ## class_name, prop_name, strlen(prop_name), prop_flags TSRMLS_CC);

#define REGISTER_ONPHP_PROPERTY_BOOL(class_name, prop_name, bool, prop_flags) \
	zend_declare_property_bool(onphp_ce_ ## class_name, prop_name, strlen(prop_name), bool, prop_flags TSRMLS_CC);

#define REGISTER_ONPHP_CLASS_CONST_LONG(class_name, const_name, value) \
	zend_declare_class_constant_long(onphp_ce_ ## class_name, const_name, strlen(const_name), (long) value TSRMLS_CC);

#define ONPHP_CLASS_IS_FINAL(class_name) \
	onphp_ce_ ## class_name->ce_flags |= ZEND_ACC_FINAL_CLASS;

#define ONPHP_CLASS_IS_ABSTRACT(class_name) \
	onphp_ce_ ## class_name->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;

#define ONPHP_READ_PROPERTY(class, property) \
	zend_read_property(Z_OBJCE_P(class), class, property, strlen(property), 1 TSRMLS_CC)

#define ONPHP_UPDATE_PROPERTY(class, property, value) \
	zend_update_property(Z_OBJCE_P(class), class, property, strlen(property), value TSRMLS_CC)

#define ONPHP_UPDATE_PROPERTY_STRING(class, property, value) \
	zend_update_property_string(Z_OBJCE_P(class), class, property, strlen(property), value TSRMLS_CC)

#define ONPHP_UPDATE_PROPERTY_STRINGL(class, property, value, length) \
	zend_update_property_stringl(Z_OBJCE_P(class), class, property, strlen(property), value, length TSRMLS_CC)

#define ONPHP_UPDATE_PROPERTY_BOOL(class, property, value) \
	zend_update_property_bool(Z_OBJCE_P(class), class, property, strlen(property), value TSRMLS_CC)

#define ONPHP_UPDATE_PROPERTY_LONG(class, property, value) \
	zend_update_property_long(Z_OBJCE_P(class), class, property, strlen(property), value TSRMLS_CC)

#define ONPHP_UPDATE_PROPERTY_NULL(class, property) \
	zend_update_property_null(Z_OBJCE_P(class), class, property, strlen(property) TSRMLS_CC)

#define ONPHP_METHOD(class_name, function_name) \
	static PHP_METHOD(onphp_ ## class_name, function_name)

#define ONPHP_THROW(exception, ...)					\
	ONPHP_THROW_NORET(exception, ##__VA_ARGS__);	\
	return;

#define ONPHP_THROW_NORET(exception, ...)	\
	ONPHP_THROW_NORET_EX(exception, 0 TSRMLS_CC, ##__VA_ARGS__)

#define ONPHP_STRINGIZE(s) ONPHP_STRINGIZE_(s)
#define ONPHP_STRINGIZE_(s) # s

#define ONPHP_THROW_NORET_EX(exception, code, message, ...)	\
	zend_throw_exception_ex(								\
		onphp_ce_ ## exception,								\
		code,												\
		ONPHP_STRINGIZE(__FILE__:__LINE__ '  ' message),	\
		##__VA_ARGS__										\
	)

#define RETURN_THIS RETURN_ZVAL(getThis(), 1, 0)
#define RETVAL_THIS RETVAL_ZVAL(getThis(), 1, 0)

#define ONPHP_COPY_ZVAL(value, copy)	\
	ALLOC_INIT_ZVAL(copy);				\
	*copy = *value;						\
	zval_copy_ctor(copy);

#define ONPHP_CLONE_ZVAL(value, copy)	\
	ALLOC_INIT_ZVAL(copy);				\
	ZVAL_ZVAL(copy, value, 1, 0);

#define ONPHP_GETTER(class_name, method_name, prop_name)				\
	ONPHP_METHOD(class_name, method_name)								\
	{																	\
		zval *prop_name = ONPHP_READ_PROPERTY(getThis(), # prop_name);	\
		RETURN_ZVAL(prop_name, 1, 0);									\
	}

#define ONPHP_SETTER_START(class_name, method_name, prop_name)			\
	ONPHP_METHOD(class_name, method_name)								\
	{																	\
		zval *prop_name;												\
																		\
		ONPHP_GET_ARGS("z", &prop_name)

#define ONPHP_SETTER_END(prop_name)										\
		ONPHP_UPDATE_PROPERTY(getThis(), # prop_name, prop_name);		\
																		\
		RETURN_THIS;													\
	}

#define ONPHP_SETTER(class_name, method_name, prop_name)	\
	ONPHP_SETTER_START(class_name, method_name, prop_name)	\
	ONPHP_SETTER_END(prop_name)

#define ONPHP_SETTER_LONG(class_name, method_name, prop_name)			\
	ONPHP_METHOD(class_name, method_name)								\
	{																	\
		int prop_name;													\
																		\
		ONPHP_GET_ARGS("l", &prop_name);								\
																		\
		ONPHP_UPDATE_PROPERTY_LONG(getThis(), # prop_name, prop_name);	\
																		\
		RETURN_THIS;													\
	}

#define ONPHP_MAKE_OBJECT(class_name, zval) \
	ALLOC_INIT_ZVAL(zval); \
	zval->value.obj = onphp_empty_object_new(onphp_ce_ ## class_name TSRMLS_CC); \
	Z_TYPE_P(zval) = IS_OBJECT;

#define ONPHP_MAKE_FOREIGN_OBJECT(class_name, zval) {				\
	zend_class_entry **cep;											\
																	\
	if (															\
		zend_lookup_class(											\
			class_name,												\
			strlen(class_name),										\
			&cep TSRMLS_CC											\
		)															\
		== FAILURE													\
	) {																\
		ONPHP_THROW(ClassNotFoundException, NULL);					\
	}																\
																	\
	ALLOC_INIT_ZVAL(zval);											\
	object_init_ex(zval, *cep);										\
	Z_TYPE_P(zval) = IS_OBJECT;										\
}

#define ONPHP_CREATOR(class_name)				\
	ONPHP_METHOD(class_name, create)			\
	{											\
		zval *object;							\
												\
		ONPHP_MAKE_OBJECT(class_name, object);	\
												\
		RETURN_ZVAL(object, 1, 1);				\
	}

#define ONPHP_STANDART_CLASS(class_name)					\
	PHPAPI zend_class_entry *onphp_ce_ ## class_name;		\
	extern zend_function_entry onphp_funcs_ ## class_name[];

#define ONPHP_FOREACH(array, value)									\
	if (zend_hash_num_elements(Z_ARRVAL_P(array)) > 0)				\
		for (														\
			zend_hash_internal_pointer_reset(Z_ARRVAL_P(array));	\
			zend_hash_get_current_data(								\
				Z_ARRVAL_P(array), (void **) &value					\
			) == SUCCESS;											\
			zend_hash_move_forward(Z_ARRVAL_P(array))				\
		)

#define ONPHP_ASSOC_ISSET(array, key) \
	zend_hash_exists(Z_ARRVAL_P(array), key, strlen(key) + 1)

#define ONPHP_ARRAY_ISSET(array, index) \
	zend_hash_index_exists(Z_ARRVAL_P(array), index)

#define ONPHP_ASSOC_UNSET(array, key) \
	zend_hash_del(Z_ARRVAL_P(array), key, strlen(key) + 1)

#define ONPHP_ASSOC_SET(array, index, value) {									\
	if (Z_TYPE_P(value) == IS_NULL) {											\
		add_assoc_null(array, index);											\
	} else if (Z_TYPE_P(value) == IS_STRING) {									\
		add_assoc_string(array, index, Z_STRVAL_P(value), 1);					\
	} else if (Z_TYPE_P(value) == IS_LONG) {									\
		add_assoc_long(array, index, Z_LVAL_P(value));							\
	} else if (Z_TYPE_P(value) == IS_BOOL) {									\
		add_assoc_bool(array, index, zval_is_true(value));						\
	} else {																	\
		ZVAL_ADDREF(value);														\
		add_assoc_zval(array, index, value);									\
	}																			\
}

#define ONPHP_ASSOC_SET_LONG(array, key, value) \
	add_assoc_long(array, key, value);

#define ONPHP_ASSOC_SET_BOOL(array, key, value) \
	add_assoc_bool(array, key, value);

#define ONPHP_ARRAY_ADD(array, value) {											\
	if (Z_TYPE_P(value) == IS_NULL) {											\
		add_next_index_null(array);												\
	} else if (Z_TYPE_P(value) == IS_STRING) {									\
		add_next_index_stringl(array, Z_STRVAL_P(value), Z_STRLEN_P(value), 1);	\
	} else if (Z_TYPE_P(value) == IS_LONG) {									\
		add_next_index_long(array, Z_LVAL_P(value));							\
	} else if (Z_TYPE_P(value) == IS_BOOL) {									\
		add_next_index_bool(array, zval_is_true(value));						\
	} else {																	\
		ZVAL_ADDREF(value);														\
		add_next_index_zval(array, value);										\
	}																			\
}

#define ONPHP_ASSOC_GET(array, key, value)	{	\
	zval **stored;								\
												\
	if (										\
		zend_hash_find(							\
			Z_ARRVAL_P(array),					\
			key,								\
			strlen(key) + 1,					\
			(void **) &stored					\
		)										\
		== FAILURE								\
	) {											\
		ONPHP_THROW_NORET(						\
			MissingElementException,			\
			"%s[%s]", # array, key				\
		);										\
	}											\
												\
	value = *stored;							\
}

#define ONPHP_ARRAY_GET(array, index, value) {	\
	zval **stored;								\
												\
	if (										\
		zend_hash_index_find(					\
			Z_ARRVAL_P(array),					\
			index,								\
			(void **) &stored					\
		)										\
		== FAILURE								\
	) {											\
		ONPHP_THROW_NORET(						\
			MissingElementException,			\
			"%s[%l]", # array, index			\
		);										\
	}											\
												\
	value = *stored;							\
}

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
