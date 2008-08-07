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

#include "onphp.h"

#include "zend_globals.h"
#include "zend_exceptions.h"

#include "core/Base/Enumeration.h"
#include "core/Exceptions.h"

ONPHP_METHOD(Enumeration, __construct)
{
	zval *id;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	zend_call_method_with_1_params(
		&getThis(),
		Z_OBJCE_P(getThis()),
		NULL,
		"setid",
		NULL,
		id
	);
}

ONPHP_METHOD(Enumeration, __sleep)
{
	zval *out;
	
	array_init(out);
	
	add_next_index_string(
		out,
		"id",
		1
	);
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Enumeration, __wakeup)
{
	zend_call_method_with_1_params(
		&getThis(),
		Z_OBJCE_P(getThis()),
		NULL,
		"setid",
		NULL,
		ONPHP_READ_PROPERTY(getThis(), "id")
	);
}

ONPHP_METHOD(Enumeration, serialize)
{
	zval *id;
	char *out = NULL;
	unsigned int length = 0;
	
	id = ONPHP_READ_PROPERTY(getThis(), "id");
	
	switch (Z_TYPE_P(id)) {
		case IS_LONG:
			
			out = emalloc(MAX_LENGTH_OF_LONG + 1 + 1);
			length = sprintf(out, "%ld", Z_LVAL_P(id));
			
			RETURN_STRINGL(out, length, 0);
			
		case IS_STRING:
			
			RETURN_ZVAL(id, 1, 0);
			
		case IS_NULL:
		default:
			
			RETURN_STRING("", 1);
	}
}

ONPHP_METHOD(Enumeration, unserialize)
{
	zval *id;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	zend_call_method_with_1_params(
		&getThis(),
		Z_OBJCE_P(getThis()),
		NULL,
		"setid",
		NULL,
		id
	);
}

ONPHP_METHOD(Enumeration, getId)
{
	zval *id;
	
	id = ONPHP_READ_PROPERTY(getThis(), "id");
	
	RETURN_ZVAL(id, 1, 0);
}

ONPHP_METHOD(Enumeration, setId)
{
	zval *id, *names;
	zval **found;
	int result;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	zend_call_method_with_0_params(
		&getThis(),
		Z_OBJCE_P(getThis()),
		NULL,
		"getnamelist",
		&names
	);
	
	if (EG(exception)) {
		return;
	}
	
	if (Z_TYPE_P(names) != IS_ARRAY) {
		zend_throw_exception_ex(
			onphp_ce_WrongStateException,
			0 TSRMLS_CC,
			"names array is not an array"
		);
		ZVAL_FREE(names);
		return;
	}
	
	switch (Z_TYPE_P(id)) {
		case IS_LONG:
			
			result =
				zend_hash_index_find(
					Z_ARRVAL_P(names),
					Z_LVAL_P(id),
					(void **) &found
				);
			
			break;
			
		case IS_STRING:
			
			result =
				zend_symtable_find(
					Z_ARRVAL_P(names),
					Z_STRVAL_P(id),
					Z_STRLEN_P(id) + 1,
					(void **) &found
				);
			
			break;
			
		case IS_NULL:
			
			result =
				zend_hash_find(
					Z_ARRVAL_P(names),
					"",
					1,
					(void **) &found
				);
			
			break;
			
		default:
			
			zend_throw_exception_ex(
				onphp_ce_WrongArgumentException,
				0 TSRMLS_CC,
				"string or an integer expected"
			);
			ZVAL_FREE(names);
			return;
	}
	
	if (result == SUCCESS) {
		ONPHP_UPDATE_PROPERTY(getThis(), "id", id);
		ONPHP_UPDATE_PROPERTY(getThis(), "name", *found);
		
		ZVAL_FREE(names);
	} else {
		if (Z_TYPE_P(id) != IS_STRING) {
			SEPARATE_ARG_IF_REF(id);
			convert_to_string(id);
		}
		
		zend_throw_exception_ex(
			onphp_ce_MissingElementException,
			0 TSRMLS_CC,
			"knows nothing about such id == {%s}",
			Z_STRVAL_P(id)
		);
		ZVAL_FREE(names);
		return;
	}
	
	RETURN_ZVAL(getThis(), 1, 0);
}

ONPHP_METHOD(Enumeration, getList)
{
	zval *enm, *out;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &enm) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	zend_call_method_with_0_params(
		&enm,
		Z_OBJCE_P(enm),
		NULL,
		"getobjectlist",
		&out
	);
	
	if (EG(exception)) {
		return;
	} else {
		RETURN_ZVAL(out, 1, 1);
	}
}

ONPHP_METHOD(Enumeration, getAnyId)
{
	RETURN_LONG(1);
}

ONPHP_METHOD(Enumeration, getObjectList)
{
	zval *names, *list;
	zval **element;
	HashTable *table;
	HashPosition pointer;
	
	ALLOC_INIT_ZVAL(list);
	array_init(list);
	
	zend_call_method_with_0_params(
		&getThis(),
		Z_OBJCE_P(getThis()),
		NULL,
		"getnamelist",
		&names
	);
	
	if (
		Z_TYPE_P(names) != IS_ARRAY
	) {
		RETURN_ZVAL(list, 1, 1);
	} else if (EG(exception)) {
		return;
	}
	
	table = Z_ARRVAL_P(names);
	
	for (
		zend_hash_internal_pointer_reset_ex(table, &pointer);
		zend_hash_get_current_data_ex(table, (void **) &element, &pointer) == SUCCESS; zend_hash_move_forward_ex(table, &pointer)
	) {
		char *key;
		unsigned long index;
		unsigned int length;
		int result;
		zval *object, *arg, *out;
		
		result = 
			zend_hash_get_current_key_ex(
				table,
				&key,
				&length,
				&index,
				0,
				&pointer
			);
		
		MAKE_STD_ZVAL(arg);
		
		if (result == HASH_KEY_IS_STRING) {
			ZVAL_STRINGL(arg, key, length, 1);
		} else if (result == HASH_KEY_IS_LONG) {
			ZVAL_LONG(arg, index);
		} else {
			zend_throw_exception_ex(
				onphp_ce_WrongStateException,
				0 TSRMLS_CC,
				"weird key found"
			);
			ZVAL_FREE(arg);
			ZVAL_FREE(list);
			return;
		}
		
		MAKE_STD_ZVAL(object);
		object->value.obj = onphp_empty_object_new(Z_OBJCE_P(getThis()) TSRMLS_CC);
		Z_TYPE_P(object) = IS_OBJECT;
		
		zend_call_method_with_1_params(
			&object,
			Z_OBJCE_P(object),
			NULL,
			"__construct",
			&out,
			arg
		);
		
		if (EG(exception)) {
			ZVAL_FREE(object);
			ZVAL_FREE(list);
			ZVAL_FREE(arg);
			return;
		} else {
			zval_dtor(arg);
			
			add_next_index_zval(list, object);
		}
	}
	
	RETURN_ZVAL(list, 1, 1);
}

ONPHP_METHOD(Enumeration, toString)
{
	zval *name = ONPHP_READ_PROPERTY(getThis(), "name");
	
	RETURN_ZVAL(name, 1, 0);
}

ONPHP_METHOD(Enumeration, getNameList)
{
	zval *names = ONPHP_READ_PROPERTY(getThis(), "names");

	RETURN_ZVAL(names, 1, 0);
}

static ONPHP_ARGINFO_ONE;

static
ZEND_BEGIN_ARG_INFO(arginfo_enum, 0) \
	ZEND_ARG_OBJ_INFO(0, enumeration, Enumeration, 0) \
ZEND_END_ARG_INFO();

zend_function_entry onphp_funcs_Enumeration[] = {
	ONPHP_ME(Enumeration, __construct,	arginfo_one, ZEND_ACC_PUBLIC |  ZEND_ACC_FINAL | ZEND_ACC_CTOR)
	ONPHP_ME(Enumeration, getList,		arginfo_enum, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Enumeration, getAnyId,		NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Enumeration, getObjectList,NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, toString,		NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, getNameList,	NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, __sleep,		NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, __wakeup,		NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, serialize,	NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, unserialize,	arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, getId,		NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, setId,		arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
