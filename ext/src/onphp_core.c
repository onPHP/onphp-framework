/* $Id$ */

#include "onphp_core.h"
#include "onphp_util.h"

#include "core/Base/Identifier.h"
#include "core/Base/Identifiable.h"
#include "core/Base/IdentifiableObject.h"
#include "core/Base/Singleton.h"
#include "core/Base/StaticFactory.h"
#include "core/Base/Stringable.h"
#include "core/Base/Named.h"
#include "core/Base/NamedObject.h"
#include "core/Base/Instantiatable.h"
#include "core/DB/Dialect.h"
#include "core/OSQL/Castable.h"
#include "core/OSQL/DBValue.h"
#include "core/OSQL/DialectString.h"
#include "core/OSQL/SQLTableName.h"

#include "core/Exceptions.h"

PHP_RINIT_FUNCTION(onphp_core)
{
	return PHP_RINIT(Singleton)(INIT_FUNC_ARGS_PASSTHRU);
}

PHP_RSHUTDOWN_FUNCTION(onphp_core)
{
	return PHP_RSHUTDOWN(Singleton)(INIT_FUNC_ARGS_PASSTHRU);
}

PHP_MINIT_FUNCTION(onphp_core)
{
	REGISTER_ONPHP_INTERFACE(Stringable);
	REGISTER_ONPHP_INTERFACE(Identifiable);
	REGISTER_ONPHP_INTERFACE(Instantiatable);
	
	REGISTER_ONPHP_INTERFACE(Named);
	REGISTER_ONPHP_IMPLEMENTS(Named, Identifiable);
	
	REGISTER_ONPHP_INTERFACE(DialectString);
	REGISTER_ONPHP_INTERFACE(SQLTableName);
	
	REGISTER_ONPHP_STD_CLASS_EX(
		Identifier,
		onphp_empty_object_new,
		onphp_funcs_Identifier
	);
	REGISTER_ONPHP_PROPERTY(Identifier, "id", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY_BOOL(Identifier, "final", 0, ZEND_ACC_PRIVATE);
	onphp_ce_Identifier->ce_flags |= ZEND_ACC_FINAL_CLASS;
	
	REGISTER_ONPHP_STD_CLASS_EX(
		IdentifiableObject,
		onphp_empty_object_new,
		onphp_funcs_IdentifiableObject
	);
	REGISTER_ONPHP_PROPERTY(IdentifiableObject, "id", ZEND_ACC_PROTECTED);
	
	REGISTER_ONPHP_IMPLEMENTS(Identifier, Identifiable);
	REGISTER_ONPHP_IMPLEMENTS(IdentifiableObject, Identifiable);

	REGISTER_ONPHP_SUB_CLASS_EX(
		NamedObject,
		IdentifiableObject,
		onphp_empty_object_new,
		onphp_funcs_NamedObject
	);
	REGISTER_ONPHP_PROPERTY(NamedObject, "name", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_IMPLEMENTS(NamedObject, Named);
	onphp_ce_NamedObject->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;

	REGISTER_ONPHP_STD_CLASS_EX(
		Singleton,
		onphp_empty_object_new,
		onphp_funcs_Singleton
	);
	onphp_ce_Singleton->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	
	REGISTER_ONPHP_STD_CLASS_EX(
		StaticFactory,
		onphp_empty_object_new,
		onphp_funcs_StaticFactory
	);
	onphp_ce_Singleton->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	
	REGISTER_ONPHP_SUB_CLASS_EX(
		Dialect,
		Singleton,
		onphp_empty_object_new,
		onphp_funcs_Dialect
	);
	REGISTER_ONPHP_IMPLEMENTS(Dialect, Instantiatable);
	onphp_ce_Dialect->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	
	REGISTER_ONPHP_STD_CLASS_EX(
		Castable,
		onphp_empty_object_new,
		onphp_funcs_Castable
	);
	REGISTER_ONPHP_PROPERTY(Castable, "cast", ZEND_ACC_PROTECTED);
	onphp_ce_Castable->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	
	REGISTER_ONPHP_SUB_CLASS_EX(
		DBValue,
		Castable,
		onphp_empty_object_new,
		onphp_funcs_DBValue
	);
	REGISTER_ONPHP_PROPERTY(DBValue, "value", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_IMPLEMENTS(DBValue, DialectString);

	PHP_MINIT_FUNCTION(Exceptions);
	
	return SUCCESS;
}
