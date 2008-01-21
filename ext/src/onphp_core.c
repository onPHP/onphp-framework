/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

// for Serializable
#include "Zend/zend_interfaces.h"

#include "ext/spl/spl_functions.h"

#include "onphp_core.h"
#include "onphp_util.h"

#include "core/Base/Enumeration.h"
#include "core/Base/Identifier.h"
#include "core/Base/Identifiable.h"
#include "core/Base/IdentifiableObject.h"
#include "core/Base/Singleton.h"
#include "core/Base/StaticFactory.h"
#include "core/Base/Stringable.h"
#include "core/Base/Named.h"
#include "core/Base/NamedObject.h"
#include "core/Base/Prototyped.h"
#include "core/Base/Instantiatable.h"

#include "core/DB/Dialect.h"
#include "core/DB/ImaginaryDialect.h"

#include "core/OSQL/Castable.h"
#include "core/OSQL/DBValue.h"
#include "core/OSQL/DialectString.h"
#include "core/OSQL/FieldTable.h"
#include "core/OSQL/SQLTableName.h"
#include "core/OSQL/Query.h"
#include "core/OSQL/QueryIdentification.h"

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
	REGISTER_ONPHP_INTERFACE(Prototyped);
	
	REGISTER_ONPHP_INTERFACE(Named);
	REGISTER_ONPHP_IMPLEMENTS(Named, Identifiable);
	
	REGISTER_ONPHP_INTERFACE(DialectString);
	REGISTER_ONPHP_INTERFACE(SQLTableName);
	REGISTER_ONPHP_IMPLEMENTS(SQLTableName, DialectString);
	
	REGISTER_ONPHP_INTERFACE(Query);
	REGISTER_ONPHP_IMPLEMENTS(Query, DialectString);
	REGISTER_ONPHP_IMPLEMENTS(Query, Identifiable);
	REGISTER_ONPHP_IMPLEMENTS(Query, Stringable);
	
	REGISTER_ONPHP_STD_CLASS_EX(Identifier);
	REGISTER_ONPHP_PROPERTY(Identifier, "id", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY_BOOL(Identifier, "final", 0, ZEND_ACC_PRIVATE);
	onphp_ce_Identifier->ce_flags |= ZEND_ACC_FINAL_CLASS;
	
	REGISTER_ONPHP_STD_CLASS_EX(IdentifiableObject);
	REGISTER_ONPHP_PROPERTY(IdentifiableObject, "id", ZEND_ACC_PROTECTED);
	
	REGISTER_ONPHP_IMPLEMENTS(Identifier, Identifiable);
	REGISTER_ONPHP_IMPLEMENTS(IdentifiableObject, Identifiable);

	REGISTER_ONPHP_SUB_CLASS_EX(NamedObject, IdentifiableObject);
	REGISTER_ONPHP_PROPERTY(NamedObject, "name", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_IMPLEMENTS(NamedObject, Named);
	onphp_ce_NamedObject->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;

	REGISTER_ONPHP_SUB_CLASS_EX(Enumeration, NamedObject);
	REGISTER_ONPHP_PROPERTY(Enumeration, "names", ZEND_ACC_PROTECTED);
	onphp_ce_Enumeration->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	
	// skipping REGISTER_ONPHP_IMPLEMENTS
	zend_class_implements(onphp_ce_Enumeration TSRMLS_CC, 1, zend_ce_serializable);

	REGISTER_ONPHP_STD_CLASS_EX(Singleton);
	onphp_ce_Singleton->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	
	REGISTER_ONPHP_STD_CLASS_EX(StaticFactory);
	onphp_ce_StaticFactory->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	
	REGISTER_ONPHP_SUB_CLASS_EX(Dialect, Singleton);
	REGISTER_ONPHP_IMPLEMENTS(Dialect, Instantiatable);
	onphp_ce_Dialect->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	
	REGISTER_ONPHP_SUB_CLASS_EX(ImaginaryDialect, Dialect);
	onphp_ce_ImaginaryDialect->ce_flags |= ZEND_ACC_FINAL_CLASS;
	
	REGISTER_ONPHP_STD_CLASS_EX(Castable);
	REGISTER_ONPHP_PROPERTY(Castable, "cast", ZEND_ACC_PROTECTED);
	onphp_ce_Castable->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	
	REGISTER_ONPHP_SUB_CLASS_EX(FieldTable, Castable);
	REGISTER_ONPHP_PROPERTY(FieldTable, "field", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_IMPLEMENTS(FieldTable, DialectString);
	onphp_ce_FieldTable->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	
	REGISTER_ONPHP_SUB_CLASS_EX(DBValue, Castable);
	REGISTER_ONPHP_PROPERTY(DBValue, "value", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_IMPLEMENTS(DBValue, DialectString);
	onphp_ce_DBValue->ce_flags |= ZEND_ACC_FINAL_CLASS;
	
	REGISTER_ONPHP_STD_CLASS_EX(QueryIdentification);
	REGISTER_ONPHP_IMPLEMENTS(QueryIdentification, Query);
	onphp_ce_QueryIdentification->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;

	return PHP_MINIT(Exceptions)(INIT_FUNC_ARGS_PASSTHRU);
}
