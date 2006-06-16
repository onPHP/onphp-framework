/* $Id$ */

#include "core/Base/IdentifiableObject.h"
#include "core/Base/Identifiable.h"
#include "core/Base/Identifier.h"
#include "core/Base/Named.h"

#include "onphp_core.h"

#include "zend_interfaces.h"

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

zend_object_value onphp_empty_object_new(zend_class_entry *class_type TSRMLS_DC)
{
	return onphp_empty_object_spawn(class_type, NULL TSRMLS_CC);
}

PHP_MINIT_FUNCTION(onphp_core)
{
	REGISTER_ONPHP_INTERFACE(Identifiable);
	REGISTER_ONPHP_INTERFACE(Named);
	REGISTER_ONPHP_IMPLEMENTS(Named, Identifiable);
	
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
