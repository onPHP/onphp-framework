/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp.h"
#include "onphp_util.h"

#include "ext/standard/php_string.h"
#include "ext/standard/php_var.h"

#include "core/Exceptions.h"
#include "core/OSQL/QuerySkeleton.h"
#include "core/Logic/LogicalObject.h"
#include "core/DB/Dialect.h"

ONPHP_METHOD(QuerySkeleton, __construct)
{
	ONPHP_CONSTRUCT_ARRAY(where);
	ONPHP_CONSTRUCT_ARRAY(whereLogic);
}

ONPHP_METHOD(QuerySkeleton, __destruct)
{
	ONPHP_PROPERTY_DESTRUCT(where);
	ONPHP_PROPERTY_DESTRUCT(whereLogic);
}

ONPHP_METHOD(QuerySkeleton, where)
{
	zval
		*exp,
		*logic,
		*where = ONPHP_READ_PROPERTY(getThis(), "where");
	
	zend_bool where_not_empty = (
		(Z_TYPE_P(where) == IS_ARRAY)
		&& (zend_hash_num_elements(Z_ARRVAL_P(where)) > 0)
	);
	
	ONPHP_GET_ARGS("z|z", &exp, &logic);
	
	if (
		(ZEND_NUM_ARGS() == 1)
		&& where_not_empty
	) {
		ONPHP_THROW(
			WrongArgumentException,
			"you have to specify expression logic"
		);
	} else {
		zval *whereLogic = ONPHP_READ_PROPERTY(getThis(), "whereLogic");
		
		if (ZEND_NUM_ARGS() == 1) {
			ALLOC_INIT_ZVAL(logic);
		}
		
		if (!where_not_empty) {
			ZVAL_NULL(logic);
		}
		
		ONPHP_ARRAY_ADD(whereLogic, logic);
		ONPHP_ARRAY_ADD(where, exp);
		
		zval_ptr_dtor(&exp);
		
		if (ZEND_NUM_ARGS() == 1) {
			ZVAL_FREE(logic);
		}
	}
	
	RETURN_THIS;
}

#define ONPHP_QUERY_SKELETON_ADD_WHERE(method_name, word)				\
ONPHP_METHOD(QuerySkeleton, method_name)								\
{																		\
	zval *exp, *logic;													\
																		\
	ONPHP_GET_ARGS("z", &exp);											\
																		\
	ALLOC_INIT_ZVAL(logic);												\
	ZVAL_STRINGL(logic, word, strlen(word) + 1, 1);						\
																		\
	ONPHP_CALL_METHOD_2_NORET(getThis(), "where", NULL, exp, logic);	\
																		\
	if (EG(exception)) {												\
		return;															\
	}																	\
																		\
	RETURN_THIS;														\
}

ONPHP_QUERY_SKELETON_ADD_WHERE(andWhere, "AND");
ONPHP_QUERY_SKELETON_ADD_WHERE(orWhere, "OR");

#undef ONPHP_QUERY_SKELETON_ADD_WHERE

ONPHP_METHOD(QuerySkeleton, toDialectString)
{
	zval *where, *whereLogic, *dialect;
	unsigned int array_count = 0;
	
	where = ONPHP_READ_PROPERTY(getThis(), "where");
	
	if (
		(Z_TYPE_P(where) == IS_ARRAY)
		&& (array_count = zend_hash_num_elements(Z_ARRVAL_P(where)))
		&& (array_count > 0)
	) {
		zval *exp, *out, *logic;
		unsigned int i, retval_len;
		char *retval;
		char output_logic = 0;
		smart_str clause = {0};
		
		ONPHP_GET_ARGS("z", &dialect);
		
		whereLogic = ONPHP_READ_PROPERTY(getThis(), "whereLogic");
		
		smart_str_appendl(&clause, " WHERE", 6);
		
		for (i = 0; i < array_count; ++i) {
			ONPHP_ARRAY_GET(where, i, exp);
			
			ONPHP_CALL_METHOD_1(exp, "todialectstring", &out, dialect);
			
			if (Z_STRLEN_P(out) > 1) {
				
				ONPHP_ARRAY_GET(whereLogic, i, logic);
				
				if (EG(exception)) {
					ZVAL_FREE(logic);
					return;
				}
				
				// can be null
				if (Z_TYPE_P(logic) == IS_STRING) {
					onphp_append_zval_to_smart_string(&clause, logic);
				}
				
				smart_str_appendl(&clause, " ", 1);
				
				onphp_append_zval_to_smart_string(&clause, out);
				
				smart_str_appendl(&clause, " ", 1);
				
				output_logic = 1;
			} else if (
				(output_logic == 0)
				&& ((i + 1) <= array_count)
				&& ONPHP_ARRAY_ISSET(whereLogic, i + 1)
			) {
				add_index_null(whereLogic, i + 1);
			}
		}
		
		retval = (char *) php_trim(clause.c, clause.len, " ", 1, NULL, 2 TSRMLS_CC);
		smart_str_0(&clause);
		smart_str_free(&clause);
		retval_len = strlen(retval);
		
		RETURN_STRINGL(retval, retval_len, 0);
	}
	
	RETURN_NULL();
}

static ONPHP_ARGINFO_LOGICAL_OBJECT;
static ONPHP_ARGINFO_LOGICAL_OBJECT_AND_ONE;
static ONPHP_ARGINFO_DIALECT;

zend_function_entry onphp_funcs_QuerySkeleton[] = {
	ONPHP_ME(QuerySkeleton, __construct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(QuerySkeleton, __destruct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_DTOR)
	ONPHP_ME(QuerySkeleton, where, arginfo_logical_object_and_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(QuerySkeleton, andWhere, arginfo_logical_object, ZEND_ACC_PUBLIC)
	ONPHP_ME(QuerySkeleton, orWhere, arginfo_logical_object, ZEND_ACC_PUBLIC)
	ONPHP_ME(QuerySkeleton, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
