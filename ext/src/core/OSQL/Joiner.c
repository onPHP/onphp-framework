/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
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

#include "core/DB/Dialect.h"

#include "core/OSQL/FromTable.h"

#include "core/Exceptions.h"

ONPHP_METHOD(Joiner, __construct)
{
	ONPHP_CONSTRUCT_ARRAY(from);
	ONPHP_CONSTRUCT_ARRAY(tables);
}

ONPHP_METHOD(Joiner, from)
{
	zval *from, *fromList;
	
	ONPHP_GET_ARGS("O", &from, onphp_ce_FromTable);
	
	fromList = ONPHP_READ_PROPERTY(getThis(), "from");
	
	ONPHP_ARRAY_ADD(fromList, from);
	
	RETURN_THIS;
}

ONPHP_METHOD(Joiner, hasJoinedTable)
{
	char *table;
	unsigned int tableLength;
	zval *tables;
	
	ONPHP_GET_ARGS("s", &table, &tableLength);
	
	tables = ONPHP_READ_PROPERTY(getThis(), "tables");
	
	RETURN_BOOL(ONPHP_ASSOC_ISSET(tables, table));
}

ONPHP_METHOD(Joiner, getTablesCount)
{
	zval *tables = ONPHP_READ_PROPERTY(getThis(), "tables");
	
	RETURN_LONG(zend_hash_num_elements(Z_ARRVAL_P(tables)));
}

#define ONPHP_JOINER_JOINERS(method_name)					\
ONPHP_METHOD(Joiner, method_name)							\
{															\
	zval													\
		*name,												\
		*join,												\
		*from = ONPHP_READ_PROPERTY(getThis(), "from"),		\
		*tables = ONPHP_READ_PROPERTY(getThis(), "tables");	\
															\
	ONPHP_GET_ARGS("o", &join);								\
															\
	ONPHP_ARRAY_ADD(from, join);							\
															\
	ONPHP_CALL_METHOD_0(join, "gettable", &name);			\
															\
	ONPHP_ASSOC_SET_BOOL(tables, Z_STRVAL_P(name), 1);		\
															\
	zval_ptr_dtor(&name);									\
															\
	RETURN_THIS;											\
}

ONPHP_JOINER_JOINERS(join);
ONPHP_JOINER_JOINERS(leftJoin);

#undef ONPHP_JOINER_JOINERS

ONPHP_METHOD(Joiner, getLastTable)
{
	zval *from = ONPHP_READ_PROPERTY(getThis(), "from");
	unsigned int count = zend_hash_num_elements(Z_ARRVAL_P(from));
	
	if (count > 0) {
		zval *name, *table;
		
		ONPHP_ARRAY_GET(from, --count, table);
		
		ONPHP_CALL_METHOD_1(table, "gettable", &name, table);
		
		RETURN_ZVAL(name, 1, 1);
	}
	
	RETURN_NULL();
}

ONPHP_METHOD(Joiner, toDialectString)
{
	zval
		*dialect,
		*from = ONPHP_READ_PROPERTY(getThis(), "from"),
		*table;
	zend_class_entry **cep;
	smart_str string = {0};
	unsigned int i = 0, count = zend_hash_num_elements(Z_ARRVAL_P(from));
	
	if (!count) {
		RETURN_NULL();
	} else {
		smart_str_appendl(&string, " FROM ", 6);
	}
	
	ONPHP_GET_ARGS("O", &dialect, onphp_ce_Dialect);
	
	ONPHP_FIND_FOREIGN_CLASS("SelectQuery", cep);
	
	for (i = 0; i < count; ++i) {
		zval *out;
		
		ONPHP_ARRAY_GET(from, i, table);
		
		if (i == 0) {
			/* nop */
		} else {
			if (ONPHP_INSTANCEOF(from, FromTable)) {
				zval *name;
				
				ONPHP_CALL_METHOD_0(table, "gettable", &name);
				
				if (instanceof_function(Z_OBJCE_P(table), *cep TSRMLS_CC)) {
					smart_str_appendl(&string, ", ", 2);
				} else {
					smart_str_appendc(&string, ' ');
				}
				
				zval_ptr_dtor(&name);
			} else {
				smart_str_appendc(&string, ' ');
			}
		}
		
		ONPHP_CALL_METHOD_1(table, "todialectstring", &out, dialect);
		
		onphp_append_zval_to_smart_string(&string, out);
		
		zval_ptr_dtor(&out);
	}
	
	smart_str_0(&string);
	
	RETURN_STRINGL(string.c, string.len, 0);
}

static
	ZEND_BEGIN_ARG_INFO(arginfo_sql_join, 0)
		ZEND_ARG_OBJ_INFO(0, sql_join, SQLJoin, 0)
	ZEND_END_ARG_INFO();

static
	ZEND_BEGIN_ARG_INFO(arginfo_sql_left_join, 0)
		ZEND_ARG_OBJ_INFO(0, sql_left_join, SQLLeftJoin, 0)
	ZEND_END_ARG_INFO();

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_DIALECT;
static ONPHP_ARGINFO_FROM_TABLE;

zend_function_entry onphp_funcs_Joiner[] = {
	ONPHP_ME(Joiner, __construct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(Joiner, from, arginfo_from_table, ZEND_ACC_PUBLIC)
	ONPHP_ME(Joiner, hasJoinedTable, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Joiner, getTablesCount, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Joiner, join, arginfo_sql_join, ZEND_ACC_PUBLIC)
	ONPHP_ME(Joiner, leftJoin, arginfo_sql_left_join, ZEND_ACC_PUBLIC)
	ONPHP_ME(Joiner, getLastTable, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Joiner, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
