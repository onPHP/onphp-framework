/* $Id$ */

#include "ext/standard/php_smart_str.h"

#include "onphp.h"

#include "core/Base/NamedObject.h"

PHPAPI zend_class_entry *onphp_ce_NamedObject;

ONPHP_METHOD(NamedObject, getName)
{
	zval *this = getThis(), *name;

	onphp_named_object *object = (onphp_named_object *) zend_object_store_get_object(
		this TSRMLS_CC
	);

	name = ONPHP_READ_PROPERTY(this, "name");

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

void onphp_append_zval_to_smart_string(smart_str *string, zval *value)
{
	zval copy;

	if (Z_TYPE_P(value) == IS_STRING) {
		smart_str_appends(string, Z_STRVAL_P(value));
	} else {
		int use_copy;
		
		zend_make_printable_zval(value, &copy, &use_copy);
		smart_str_appends(string, Z_STRVAL(copy));
		
		if (use_copy) {
			zval_dtor(&copy);
		}
	}
}

ONPHP_METHOD(NamedObject, toString)
{
	zval *this = getThis(), *id, *name;
	zval id_copy, name_copy;
	int use_id_copy, use_name_copy;
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
	
	if (
		Z_TYPE_P(first) != IS_OBJECT
		|| Z_TYPE_P(second) != IS_OBJECT
		|| !instanceof_function(Z_OBJCE_P(first), onphp_ce_NamedObject TSRMLS_CC)
		|| !instanceof_function(Z_OBJCE_P(second), onphp_ce_NamedObject TSRMLS_CC)
	) {
		zend_error(E_ERROR, "NamedObject::compareNames() expects two NamedObject instances");
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
	ONPHP_ME(NamedObject, compareNames,	arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(NamedObject, toString,		NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
