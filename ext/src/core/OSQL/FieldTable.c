/* $Id$ */

#include "zend_interfaces.h"

#include "onphp.h"
#include "onphp_core.h"

#include "core/DB/Dialect.h"
#include "core/OSQL/FieldTable.h"

PHPAPI zend_class_entry *onphp_ce_FieldTable;

ONPHP_METHOD(FieldTable, __construct)
{
	zval *field;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &field) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(getThis(), "field", field);
}

ONPHP_METHOD(FieldTable, toDialectString)
{
	zval *dialect, *cast, *field, *out;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &dialect) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	field = ONPHP_READ_PROPERTY(getThis(), "field");
	
	SEPARATE_ZVAL_TO_MAKE_IS_REF(&field);
	
	zend_call_method_with_1_params(
		&dialect,
		Z_OBJCE_P(dialect),
		NULL,
		"fieldtostring",
		&out,
		field
	);
	
	if (EG(exception)) {
		return;
	}
	
	cast = ONPHP_READ_PROPERTY(getThis(), "cast");
	
	if (Z_STRLEN_P(cast)) {
		zend_call_method_with_2_params(
			&dialect,
			Z_OBJCE_P(dialect),
			NULL,
			"tocasted",
			&out,
			out,
			cast
		);
		
		if (EG(exception)) {
			return;
		}
	} else {
		// nothing
	}
	
	RETURN_ZVAL(out, 1, 1);
}


static ONPHP_ARGINFO_DIALECT;

static
ZEND_BEGIN_ARG_INFO(arginfo_dialect_string, 0) \
	ZEND_ARG_OBJ_INFO(0, field, DialectString, 0) \
ZEND_END_ARG_INFO()

zend_function_entry onphp_funcs_FieldTable[] = {
	ONPHP_ME(FieldTable, __construct, arginfo_dialect_string, ZEND_ACC_PUBLIC)
	ONPHP_ME(FieldTable, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
