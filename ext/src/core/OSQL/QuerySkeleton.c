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
	zval *where, *whereLogic, *exp, *logic = NULL;
	zval *copy, *copy2;
	
	MAKE_STD_ZVAL(copy);
	
	ONPHP_GET_ARGS("z|z", &exp, &logic);
	
	*copy = *exp;
	zval_copy_ctor(copy);
	
	where = ONPHP_READ_PROPERTY(getThis(), "where");
	
	if (
		zend_hash_num_elements(Z_ARRVAL_P(where)) != 0
		&& Z_TYPE_P(logic) == IS_NULL
	) {
		zend_throw_exception_ex(
			onphp_ce_WrongArgumentException,
			0 TSRMLS_CC,
			"you have to specify expression logic"
		);
		return;
	} else {
		if (
			zend_hash_num_elements(Z_ARRVAL_P(where)) == 0
			&& logic
			&& Z_TYPE_P(logic) != IS_NULL
		) {
			ZVAL_NULL(logic);
		}
		
		whereLogic = ONPHP_READ_PROPERTY(getThis(), "whereLogic");
		
		if (
			logic
			&& (Z_TYPE_P(logic) != IS_NULL)
		) {
			MAKE_STD_ZVAL(copy2);
			*copy2 = *logic;
			zval_copy_ctor(copy2);
			
			add_next_index_zval(whereLogic, copy2);
		} else
			add_next_index_null(whereLogic);
		
		add_next_index_zval(where, copy);
	}
	
	RETURN_THIS;
}

ONPHP_METHOD(QuerySkeleton, andWhere)
{
	zval *exp, *logic, *retval;
	
	MAKE_STD_ZVAL(logic);
	ZVAL_STRING(logic, "AND", 1);
	
	ONPHP_GET_ARGS("z", &exp);
	
	zend_call_method_with_2_params(
		&getThis(),
		onphp_ce_QuerySkeleton,
		NULL,
		"where",
		&retval,
		exp,
		logic
	);
	
	ZVAL_FREE(logic);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(retval, 1, 0);
}

ONPHP_METHOD(QuerySkeleton, orWhere)
{
	zval *exp, *logic, *retval;
	
	MAKE_STD_ZVAL(logic);
	ZVAL_STRING(logic, "OR", 1);
	
	ONPHP_GET_ARGS("z", &exp);
	
	zend_call_method_with_2_params(
		&getThis(),
		onphp_ce_QuerySkeleton,
		NULL,
		"where",
		&retval,
		exp,
		logic
	);
	
	ZVAL_FREE(logic);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(retval, 1, 0);
}

ONPHP_METHOD(QuerySkeleton, toDialectString)
{
	zval *where, *whereLogic, *dialect;
	
	ONPHP_GET_ARGS("z", &dialect);
	
	where = ONPHP_READ_PROPERTY(getThis(), "where");
	
	if (
		Z_TYPE_P(where) != IS_NULL
		&& zend_hash_num_elements(Z_ARRVAL_P(where)) != 0
	) {
		zval *outputLogic, *exp;
		zval **data;
		int i, array_count, retval_len;
		char *retval;
		
		MAKE_STD_ZVAL(outputLogic);
		ZVAL_FALSE(outputLogic);
		
		smart_str clause = {0};
		smart_str_appendl(&clause, " WHERE", 6);
		
		array_count = zend_hash_num_elements(Z_ARRVAL_P(where));
		
		for (i = 0; i < array_count; i++) {
			if (
				zend_hash_index_find(
					Z_ARRVAL_P(where),
					i,
					(void **) &data
				) == SUCCESS
			) {
				zend_call_method_with_1_params(
					data,
					Z_OBJCE_PP(data),
					NULL,
					"todialectstring",
					&exp,
					dialect
				);
				
				if (EG(exception)) {
					ZVAL_FREE(outputLogic);
					return;
				}
				
				whereLogic = ONPHP_READ_PROPERTY(getThis(), "whereLogic");
				
				if (exp) {
					if (
						zend_hash_index_find(
							Z_ARRVAL_P(whereLogic),
							i,
							(void **) &data
						) == SUCCESS
					) {
						onphp_append_zval_to_smart_string(&clause, *data);
						smart_str_appendl(&clause, " ", 1);
						onphp_append_zval_to_smart_string(&clause, exp);
						smart_str_appendl(&clause, " ", 1);
						
						ZVAL_TRUE(outputLogic);
					}
					
					ZVAL_FREE(exp);
				}
				
				if (
					!Z_BVAL_P(outputLogic)
					&& (
						zend_hash_index_find(
							Z_ARRVAL_P(whereLogic),
							i + 1,
							(void **) &data
						) == SUCCESS
					)
				) {
					add_index_null(whereLogic, i + 1);
				}
			}
		}
		
		retval = (char *) php_trim(clause.c, clause.len, " ", 1, NULL, 2);
		smart_str_0(&clause);
		smart_str_free(&clause);
		retval_len = strlen(retval);
		zval_ptr_dtor(&outputLogic);
		
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
