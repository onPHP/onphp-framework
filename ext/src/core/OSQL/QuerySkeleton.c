/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: SelectField.c 3876 2007-07-27 11:19:39Z voxus $ */


#include "onphp_core.h"
#include "onphp_util.h"

#include "zend_exceptions.h"
#include "ext/standard/php_string.h"

#include "core/Exceptions.h"
#include "core/OSQL/QuerySkeleton.h"
#include "core/Logic/LogicalObject.h"


ONPHP_METHOD(QuerySkeleton, __construct)
{
	zval *where, *whereLogic;

	/* init */
	where = ONPHP_READ_PROPERTY(getThis(), "where");
	array_init(where);
	
	whereLogic = ONPHP_READ_PROPERTY(getThis(), "whereLogic");
	array_init(whereLogic);
}

ONPHP_METHOD(QuerySkeleton, __destruct)
{
	zval **data;
	
	if (SUCCESS == zend_hash_find(HASH_OF(this_ptr), "where",
					sizeof("where"), (void**)&data)) {
		zval_ptr_dtor(data);
	}
	
	if (SUCCESS == zend_hash_find(HASH_OF(this_ptr), "whereLogic",
					sizeof("whereLogic"), (void**)&data)) {
		zval_ptr_dtor(data);
	}
}

ONPHP_METHOD(QuerySkeleton, where)
{
	zval *where, *whereLogic, *exp, *logic;
	
	if (
		zend_parse_parameters(
			ZEND_NUM_ARGS() TSRMLS_CC,
			"z|z",
			&exp,
			&logic
		)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	where = ONPHP_READ_PROPERTY(getThis(), "where");
	
	if (
		Z_TYPE_P(where) != IS_NULL
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
			Z_TYPE_P(where) == IS_NULL
			&& Z_TYPE_P(logic) != IS_NULL
		) {
			logic = NULL;
		}
	
		whereLogic = ONPHP_READ_PROPERTY(getThis(), "whereLogic");
		
		add_next_index_zval(whereLogic, logic);
		add_next_index_zval(where, exp);
	}
	
	RETURN_ZVAL(getThis(), 1, 0);
}

ONPHP_METHOD(QuerySkeleton, andWhere)
{
	zval *exp, *logic, *retval;

    MAKE_STD_ZVAL(logic);
    ZVAL_STRING(logic, "AND", 1);
	
	if (
		zend_parse_parameters(
			ZEND_NUM_ARGS() TSRMLS_CC,
			"z",
			&exp
		)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	zend_call_method_with_2_params(
			&getThis(),
			onphp_ce_QuerySkeleton,
			NULL,
			"where",
			&retval,
			exp,
			logic
		);
	
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
	
	if (
		zend_parse_parameters(
			ZEND_NUM_ARGS() TSRMLS_CC,
			"z",
			&exp
		)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	zend_call_method_with_2_params(
			&getThis(),
			onphp_ce_QuerySkeleton,
			NULL,
			"where",
			&retval,
			exp,
			logic
		);
	
	if (EG(exception)) {
		return;
	}
   
   	RETURN_ZVAL(retval, 1, 0);
}

static ONPHP_ARGINFO_LOGICAL_OBJECT;
static ONPHP_ARGINFO_LOGICAL_OBJECT_AND_ONE;

zend_function_entry onphp_funcs_QuerySkeleton[] = {
	ONPHP_ME(QuerySkeleton, __construct, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(QuerySkeleton, __destruct, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(QuerySkeleton, where, arginfo_logical_object_and_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(QuerySkeleton, andWhere, arginfo_logical_object, ZEND_ACC_PUBLIC)
	ONPHP_ME(QuerySkeleton, orWhere, arginfo_logical_object, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
