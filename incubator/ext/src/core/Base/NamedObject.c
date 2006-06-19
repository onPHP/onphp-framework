/* $Id$ */

#include "onphp.h"
#include "onphp_util.h"

#include "core/Base/NamedObject.h"

PHPAPI zend_class_entry *onphp_ce_NamedObject;

static
ZEND_BEGIN_ARG_INFO(arginfo_two_named_objects, 0)
	ZEND_ARG_OBJ_INFO(0, Named, Named, 0)
	ZEND_ARG_OBJ_INFO(0, Named, Named, 0)
ZEND_END_ARG_INFO();

ONPHP_METHOD(NamedObject, getName)
{
	zval *name = ONPHP_READ_PROPERTY(getThis(), "name");

	RETURN_ZVAL(name, 1, 0);
}

ONPHP_METHOD(NamedObject, setName)
{
	zval *this = getThis(), *name;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &name) == SUCCESS) {
		ONPHP_UPDATE_PROPERTY(this, "name", name);
	}

	RETURN_ZVAL(this, 1, 0);
}

ONPHP_METHOD(NamedObject, toString)
{
	zval *this = getThis();
	smart_str string = {0};

	onphp_append_zval_to_smart_string(&string, ONPHP_READ_PROPERTY(this, "id"));
	smart_str_appends(&string, ": ");
	onphp_append_zval_to_smart_string(&string, ONPHP_READ_PROPERTY(this, "name"));
	smart_str_0(&string);

	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(NamedObject, compareNames)
{
	zval *first, *second;

	if (
		(ZEND_NUM_ARGS() != 2)
		|| zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zz", &first, &second) == FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	RETURN_LONG(
		strcasecmp(
			Z_STRVAL_P(ONPHP_READ_PROPERTY(first, "name")),
			Z_STRVAL_P(ONPHP_READ_PROPERTY(second, "name"))
		)
	);
}

zend_function_entry onphp_funcs_NamedObject[] = {
	ONPHP_ME(NamedObject, getName,		NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(NamedObject, setName,		arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(NamedObject, compareNames,	arginfo_two_named_objects, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(NamedObject, toString,		NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
