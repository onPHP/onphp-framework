/* $Id$ */

#include "onphp.h"
#include "onphp_core.h"

#include "core/DB/DBValue.h"
#include "core/OSQL/DialectString.h"

PHPAPI zend_class_entry *onphp_ce_DBValue;

ONPHP_METHOD(DBValue, create)
{
	zval *object, *value;

	MAKE_STD_ZVAL(object);

	object->value.obj = onphp_empty_object_new(onphp_ce_DBValue TSRMLS_CC);
	Z_TYPE_P(object) = IS_OBJECT;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(object, "value", value);

	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(DBValue, __construct)
{
	zval *this = getThis(), *value;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(this, "value", value);
	
	if (Z_TYPE_P(value) == IS_LONG) {
		ONPHP_UPDATE_PROPERTY(this, "unquotable", 0);
	}
}

ONPHP_METHOD(DBValue, getValue)
{
	zval *value = ONPHP_READ_PROPERTY(getThis(), "value");

	RETURN_ZVAL(value, 1, 0);
}

ONPHP_METHOD(DBValue, toDialectString)
{
	// TODO: implement.
}

zend_function_entry onphp_funcs_DBValue[] = {
	ONPHP_ME(DBValue, create, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(DBValue, __construct, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBValue, getValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(DBValue, toDialectString, arginfo_dialect, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
