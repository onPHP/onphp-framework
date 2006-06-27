/* $Id$ */

#include "zend_interfaces.h"

#include "onphp.h"
#include "onphp_core.h"

#include "core/DB/Dialect.h"
#include "core/OSQL/DBValue.h"

PHPAPI zend_class_entry *onphp_ce_DBValue;

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_DIALECT;

ONPHP_METHOD(DBValue, create)
{
	zval *object, *value;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	MAKE_STD_ZVAL(object);

	object->value.obj = onphp_empty_object_new(onphp_ce_DBValue TSRMLS_CC);
	Z_TYPE_P(object) = IS_OBJECT;
	
	ONPHP_UPDATE_PROPERTY(object, "value", value);

	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(DBValue, __construct)
{
	zval *value;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(getThis(), "value", value);
}

ONPHP_METHOD(DBValue, getValue)
{
	zval *value = ONPHP_READ_PROPERTY(getThis(), "value");

	RETURN_ZVAL(value, 1, 0);
}

ONPHP_METHOD(DBValue, toDialectString)
{
	zval *dialect, *cast, *value, *out;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &dialect) == FAILURE) {
		return;
	}
	
	value = ONPHP_READ_PROPERTY(getThis(), "value");
	
	zend_call_method_with_1_params(
		&dialect,
		Z_OBJCE_P(dialect),
		NULL,
		// lowercased because of external class
		"quotevalue",
		&out,
		value
	);
	
	if (!out) {
		// exception was thrown
		return;
	}
	
	cast = ONPHP_READ_PROPERTY(getThis(), "cast");
	
	if (Z_STRLEN_P(cast)) {
		zend_call_method_with_2_params(
			&dialect,
			Z_OBJCE_P(dialect),
			NULL,
			"toCasted",
			&out,
			out,
			cast
		);
	} else {
		// nothing
	}
	
	RETURN_ZVAL(out, 1, 1);
}

zend_function_entry onphp_funcs_DBValue[] = {
	ONPHP_ME(DBValue, create, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(DBValue, __construct, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBValue, getValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBValue, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
