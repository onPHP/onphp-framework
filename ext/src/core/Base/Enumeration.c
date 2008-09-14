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

#include "core/Base/Enumeration.h"

#include "core/Exceptions.h"

ONPHP_METHOD(Enumeration, __construct)
{
	zval *id;
	
	ONPHP_GET_ARGS("z", &id);
	
	ONPHP_CALL_METHOD_1(getThis(), "setid", NULL, id);
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
	
	ONPHP_GET_ARGS("z", &id);
	
	ONPHP_CALL_METHOD_1(getThis(), "setid", NULL, id);
}

ONPHP_GETTER(Enumeration, getId, id);

ONPHP_METHOD(Enumeration, setId)
{
	zval *id, *names;
	zval **found;
	int result;
	
	ONPHP_GET_ARGS("z", &id);
	
	ONPHP_CALL_METHOD_0(getThis(), "getnamelist", &names);
	
	if (Z_TYPE_P(names) != IS_ARRAY) {
		ZVAL_FREE(names);
		
		ONPHP_THROW(WrongStateException, "names array is not an array");
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
			
			ZVAL_FREE(names);
			
			ONPHP_THROW(WrongArgumentException, "string or an integer expected");
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
		
		ZVAL_FREE(names);
		
		ONPHP_THROW_NORET(
			MissingElementException,
			"knows nothing about such id == {%s}",
			Z_STRVAL_P(id)
		);
		
		ZVAL_FREE(id);
		return;
	}
	
	RETURN_THIS;
}

ONPHP_METHOD(Enumeration, getList)
{
	zval *enm, *out;
	
	ONPHP_GET_ARGS("O", &enm, onphp_ce_Enumeration);
	
	ONPHP_CALL_METHOD_0(enm, "getobjectlist", &out);
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Enumeration, getAnyId)
{
	RETURN_LONG(1);
}

ONPHP_METHOD(Enumeration, getObjectList)
{
	zval *names, *list, **element;
	
	ALLOC_INIT_ZVAL(list);
	array_init(list);
	
	ONPHP_CALL_METHOD_0(getThis(), "getnamelist", &names);
	
	if (
		Z_TYPE_P(names) != IS_ARRAY
	) {
		ZVAL_FREE(names);
		RETURN_ZVAL(list, 1, 1);
	}
	
	ONPHP_FOREACH(names, element) {
		zval *object, *arg;
		char *key;
		ulong length;
		unsigned int result;
		
		ALLOC_INIT_ZVAL(object);
		object->value.obj = onphp_empty_object_new(Z_OBJCE_P(getThis()) TSRMLS_CC);
		Z_TYPE_P(object) = IS_OBJECT;
		
		result =
			zend_hash_get_current_key(
				Z_ARRVAL_P(names),
				&key,
				&length,
				0
			);
		
		ALLOC_INIT_ZVAL(arg);
		
		if (result == HASH_KEY_IS_STRING) {
			ZVAL_STRINGL(arg, key, length, 1);
		} else if (result == HASH_KEY_IS_LONG) {
			ZVAL_LONG(arg, length);
		} else {
			ZVAL_FREE(arg);
			ZVAL_FREE(list);
			ZVAL_FREE(object);
			ONPHP_THROW(WrongStateException, "weird key found");
		}
		
		ONPHP_CALL_METHOD_1_NORET(object, "__construct", NULL, arg);
		
		zval_ptr_dtor(&arg);
		
		if (EG(exception)) {
			ZVAL_FREE(object);
			ZVAL_FREE(list);
			return;
		} else {
			add_next_index_zval(list, object);
		}
	}
	
	ZVAL_FREE(names);
	
	RETURN_ZVAL(list, 1, 1);
}

ONPHP_GETTER(Enumeration, toString, name);
ONPHP_GETTER(Enumeration, getNameList, names);

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
	ONPHP_ME(Enumeration, serialize,	NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, unserialize,	arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, getId,		NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Enumeration, setId,		arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
