/* $Id$ */

#include "onphp.h"

#include "core/Main/Identifier.h"

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

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == SUCCESS) {
		zend_update_property(
			onphp_ce_Identifier,
			object,
			"id",
			strlen("id"),
			id TSRMLS_CC
		);
	}

	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(Identifier, getId)
{
	zval *this = getThis(), *id;

	id = zend_read_property(Z_OBJCE_P(this), this, "id", strlen("id"), 1 TSRMLS_CC);

	RETURN_ZVAL(id, 1, 0);
}

ONPHP_METHOD(Identifier, setId)
{
	zval *this = getThis(), *id;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == SUCCESS) {
		zend_update_property(
			onphp_ce_Identifier,
			this,
			"id",
			strlen("id"),
			id TSRMLS_CC
		);
	}

	RETURN_ZVAL(this, 1, 0);
}

ONPHP_METHOD(Identifier, finalize)
{
	zval *this = getThis(), *true;

	ALLOC_INIT_ZVAL(true);
	ZVAL_TRUE(true);
	
	zend_update_property(
		onphp_ce_Identifier,
		this,
		"final",
		strlen("final"),
		true TSRMLS_CC
	);

	RETURN_ZVAL(this, 1, 0);
}

ONPHP_METHOD(Identifier, isFinalized)
{
	zval *this = getThis(), *final;

	final = zend_read_property(Z_OBJCE_P(this), this, "final", strlen("final"), 1 TSRMLS_CC);

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
