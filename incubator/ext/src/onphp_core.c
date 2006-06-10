/* $Id$ */

#include "onphp_core.h"

#include "zend_interfaces.h"

// TODO: implement Identifier
// TODO: implement Identifier's checking in IdentifiableObject::getId()

PHPAPI zend_class_entry *onphp_ce_Identifiable;
PHPAPI zend_class_entry *onphp_ce_IdentifiableObject;

static zend_object_handlers onphp_identifiable_object_handlers;

static void onphp_identifiable_object_free_storage(void *object TSRMLS_DC)
{
	zval_ptr_dtor(&object);
	efree(object);
}

static zend_object_value onphp_identifiable_object_new_ex(
	zend_class_entry *class_type,
	onphp_identifiable_object **identifiable TSRMLS_DC
)
{
	zend_object_value objval;
	onphp_identifiable_object *intern;
	zval *tmp;

	intern = emalloc(sizeof(onphp_identifiable_object));
	memset(intern, 0, sizeof(onphp_identifiable_object));

	if (identifiable)
		*identifiable = intern;
	
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
		(zend_objects_free_object_storage_t) onphp_identifiable_object_free_storage,
		NULL TSRMLS_CC
	);
	
	objval.handlers = &onphp_identifiable_object_handlers;
	
	return objval;
}

static zend_object_value onphp_identifiable_object_new(zend_class_entry *class_type TSRMLS_DC)
{
	return onphp_identifiable_object_new_ex(class_type, NULL TSRMLS_CC);
}

ONPHP_METHOD(IdentifiableObject, wrap)
{
	zval *id = NULL;
	zval *object = NULL;
	zend_class_entry *ce = onphp_ce_IdentifiableObject;

	MAKE_STD_ZVAL(object);

	object->value.obj = onphp_identifiable_object_new(ce TSRMLS_CC);
	Z_TYPE_P(object) = IS_OBJECT;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == SUCCESS) {
		onphp_identifiable_object *identifiable =
			(onphp_identifiable_object *) zend_object_store_get_object(
				object TSRMLS_CC
			);

		ALLOC_INIT_ZVAL(identifiable->id);
		ZVAL_ZVAL(identifiable->id, id, 1, 1);

		zval_ptr_dtor(&id);
	}
	
	RETURN_ZVAL(object, 1, 0);
}

ONPHP_METHOD(IdentifiableObject, getId)
{
	onphp_identifiable_object *object = (onphp_identifiable_object *) zend_object_store_get_object(
		getThis() TSRMLS_CC
	);

	if (object->id) {
		RETURN_ZVAL(object->id, 1, 0);
	} else {
		RETURN_NULL();
	}
}

ONPHP_METHOD(IdentifiableObject, setId)
{
	zval *id = NULL;

	onphp_identifiable_object *object = (onphp_identifiable_object *) zend_object_store_get_object(
		getThis() TSRMLS_CC
	);
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &id) == SUCCESS) {
		ALLOC_INIT_ZVAL(object->id);
		ZVAL_ZVAL(object->id, id, 1, 1);

		zval_ptr_dtor(&id);
	}

	RETURN_ZVAL(getThis(), 1, 0);
}

static
ZEND_BEGIN_ARG_INFO(arginfo_Identifiable_setId, 0)
	ZEND_ARG_INFO(0, id)
ZEND_END_ARG_INFO()

static
zend_function_entry onphp_funcs_Identifiable[] = {
	ONPHP_ABSTRACT_ME(Identifiable, getId, NULL)
	ONPHP_ABSTRACT_ME(Identifiable, setId, arginfo_Identifiable_setId)
	{NULL, NULL, NULL}
};


static
ZEND_BEGIN_ARG_INFO(arginfo_IdentifiableObject_setId, 0)
	ZEND_ARG_INFO(0, id)
ZEND_END_ARG_INFO()

static zend_function_entry onphp_funcs_IdentifiableObject[] = {
	ONPHP_ME(IdentifiableObject, getId,	NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(IdentifiableObject, setId,	arginfo_IdentifiableObject_setId, ZEND_ACC_PUBLIC)
	ONPHP_ME(IdentifiableObject, wrap,	arginfo_IdentifiableObject_setId, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	{NULL, NULL, NULL}
};


PHP_MINIT_FUNCTION(onphp_core)
{
	REGISTER_ONPHP_INTERFACE(Identifiable);
	
	REGISTER_ONPHP_STD_CLASS_EX(
		IdentifiableObject,
		onphp_identifiable_object_new,
		onphp_funcs_IdentifiableObject
	);

	REGISTER_ONPHP_IMPLEMENTS(IdentifiableObject, Identifiable);
	
	memcpy(&onphp_identifiable_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
}
