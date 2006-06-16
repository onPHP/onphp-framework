/* $Id$ */

#include "onphp.h"

#include "core/Base/Identifier.h"

PHPAPI zend_class_entry *onphp_ce_Identifier;

ONPHP_METHOD(Identifier, create)
{
	zval *object;

	MAKE_STD_ZVAL(object);

	object->value.obj = onphp_empty_object_new(onphp_ce_Identifier TSRMLS_CC);
	Z_TYPE_P(object) = IS_OBJECT;

	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(Identifier, wrap)
{
	zval *object, *id;

	MAKE_STD_ZVAL(object);

	object->value.obj = onphp_empty_object_new(onphp_ce_Identifier TSRMLS_CC);
	Z_TYPE_P(object) = IS_OBJECT;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(object, "id", id);

	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(Identifier, getId)
{
	zval *this = getThis(), *id;

	id = ONPHP_READ_PROPERTY(this, "id");

	RETURN_ZVAL(id, 1, 0);
}

ONPHP_METHOD(Identifier, setId)
{
	zval *this = getThis(), *id;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	ONPHP_UPDATE_PROPERTY(this, "id", id);

	RETURN_ZVAL(this, 1, 0);
}

ONPHP_METHOD(Identifier, finalize)
{
	zval *this = getThis(), *true;

	ALLOC_INIT_ZVAL(true);
	ZVAL_TRUE(true);

	ONPHP_UPDATE_PROPERTY(this, "final", true);

	RETURN_ZVAL(this, 1, 0);
}

ONPHP_METHOD(Identifier, isFinalized)
{
	zval *this = getThis(), *final;

	final = ONPHP_READ_PROPERTY(this, "final");

	RETURN_ZVAL(final, 1, 0);
}

zend_function_entry onphp_funcs_Identifier[] = {
	ONPHP_ME(Identifier, create,		NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Identifier, wrap,			arginfo_id, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Identifier, getId,			NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Identifier, setId,			arginfo_id, ZEND_ACC_PUBLIC)
	ONPHP_ME(Identifier, finalize,		NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Identifier, isFinalized,	NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
