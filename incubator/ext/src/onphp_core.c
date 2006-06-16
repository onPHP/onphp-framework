/* $Id$ */

#include "onphp_core.h"

#include "zend_interfaces.h"

PHPAPI zend_class_entry *onphp_ce_Identifier;
PHPAPI zend_class_entry *onphp_ce_Identifiable;
PHPAPI zend_class_entry *onphp_ce_IdentifiableObject;

static zend_object_handlers zend_std_obj_handlers;

static void onphp_empty_object_free_storage(void *object TSRMLS_DC)
{
	onphp_empty_object *empty = (onphp_empty_object *) object;

	zend_object_std_dtor(&empty->std TSRMLS_CC);

	efree(object);
}

static zend_object_value onphp_empty_object_spawn(
	zend_class_entry *class_type,
	onphp_empty_object **object TSRMLS_DC
)
{
	zend_object_value objval;
	onphp_empty_object *intern;
	zval *tmp;

	intern = emalloc(sizeof(onphp_empty_object));
	memset(intern, 0, sizeof(onphp_empty_object));

	if (object)
		*object = intern;

	zend_object_std_init(&intern->std, class_type TSRMLS_CC);

	zend_hash_copy(
		intern->std.properties,
		&class_type->default_properties,
		(copy_ctor_func_t) zval_add_ref,
		(void *) &tmp,
		sizeof(zval *)
	);

	objval.handle = zend_objects_store_put(
		intern,
		(zend_objects_store_dtor_t) zend_objects_destroy_object,
		(zend_objects_free_object_storage_t) onphp_empty_object_free_storage,
		NULL TSRMLS_CC
	);
	
	objval.handlers = &zend_std_obj_handlers;

	return objval;
}

static zend_object_value onphp_empty_object_new(zend_class_entry *class_type TSRMLS_DC)
{
	return onphp_empty_object_spawn(class_type, NULL TSRMLS_CC);
}

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

ONPHP_METHOD(IdentifiableObject, wrap)
{
	zval *object, *id;

	MAKE_STD_ZVAL(object);

	object->value.obj = onphp_empty_object_new(onphp_ce_IdentifiableObject TSRMLS_CC);
	Z_TYPE_P(object) = IS_OBJECT;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == SUCCESS) {
		zend_update_property(
			onphp_ce_IdentifiableObject,
			object,
			"id",
			strlen("id"),
			id TSRMLS_CC
		);
	}
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(IdentifiableObject, getId)
{
	zval *this = getThis(), *id;

	onphp_identifiable_object *object = (onphp_identifiable_object *) zend_object_store_get_object(
		this TSRMLS_CC
	);

	id = zend_read_property(Z_OBJCE_P(this), this, "id", strlen("id"), 1 TSRMLS_CC);

	if (
		Z_TYPE_P(id) == IS_OBJECT
		&& instanceof_function(Z_OBJCE_P(id), onphp_ce_Identifier TSRMLS_CC)
	) {
		if (zval_is_true(zend_read_property(Z_OBJCE_P(id), id, "final", strlen("final"), 1 TSRMLS_CC))) {
			id = zend_read_property(Z_OBJCE_P(id), id, "id", strlen("id"), 1 TSRMLS_CC);
		}
	}

	RETURN_ZVAL(id, 1, 0);
}

ONPHP_METHOD(IdentifiableObject, setId)
{
	zval *this = getThis(), *id;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == SUCCESS) {
		zend_update_property(
			onphp_ce_IdentifiableObject,
			this,
			"id",
			strlen("id"),
			id TSRMLS_CC
		);
	}

	RETURN_ZVAL(this, 1, 0);
}

static
ZEND_BEGIN_ARG_INFO(arginfo_id, 0)
	ZEND_ARG_INFO(0, id)
ZEND_END_ARG_INFO()

static
zend_function_entry onphp_funcs_Identifiable[] = {
	ONPHP_ABSTRACT_ME(Identifiable, getId, NULL)
	ONPHP_ABSTRACT_ME(Identifiable, setId, arginfo_id)
	{NULL, NULL, NULL}
};

static zend_function_entry onphp_funcs_IdentifiableObject[] = {
	ONPHP_ME(IdentifiableObject, getId,	NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(IdentifiableObject, setId,	arginfo_id, ZEND_ACC_PUBLIC)
	ONPHP_ME(IdentifiableObject, wrap,	arginfo_id, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	{NULL, NULL, NULL}
};

static zend_function_entry onphp_funcs_Identifier[] = {
	ONPHP_ME(Identifier, create,		NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Identifier, wrap,			arginfo_id, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Identifier, getId,			NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Identifier, setId,			arginfo_id, ZEND_ACC_PUBLIC)
	ONPHP_ME(Identifier, finalize,		NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Identifier, isFinalized,	NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};

PHP_MINIT_FUNCTION(onphp_core)
{
	REGISTER_ONPHP_INTERFACE(Identifiable);
	
	REGISTER_ONPHP_STD_CLASS_EX(
		Identifier,
		onphp_empty_object_new,
		onphp_funcs_Identifier
	);
	REGISTER_ONPHP_PROPERTY(Identifier, "id", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY(Identifier, "final", ZEND_ACC_PRIVATE);
	onphp_ce_Identifier->ce_flags |= ZEND_ACC_FINAL;
	
	REGISTER_ONPHP_STD_CLASS_EX(
		IdentifiableObject,
		onphp_empty_object_new,
		onphp_funcs_IdentifiableObject
	);
	REGISTER_ONPHP_PROPERTY(IdentifiableObject, "id", ZEND_ACC_PROTECTED);
	
	REGISTER_ONPHP_IMPLEMENTS(Identifier, Identifiable);
	REGISTER_ONPHP_IMPLEMENTS(IdentifiableObject, Identifiable);
	
	memcpy(&zend_std_obj_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
}
