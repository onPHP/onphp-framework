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

#include "core/Base/Aliased.h"
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
#include "core/Base/Ternary.h"

#include "core/DB/Dialect.h"
#include "core/DB/ImaginaryDialect.h"

#include "core/Form/PlainForm.h"
#include "core/Form/RegulatedForm.h"
#include "core/Form/Form.h"
#include "core/Form/FormField.h"

#include "core/Form/Filters/BaseFilter.h"
#include "core/Form/Filters/Filtrator.h"

#include "core/Form/Primitives/BasePrimitive.h"
#include "core/Form/Primitives/RangedPrimitive.h"
#include "core/Form/Primitives/ComplexPrimitive.h"
#include "core/Form/Primitives/ListedPrimitive.h"
#include "core/Form/Primitives/FiltrablePrimitive.h"
#include "core/Form/Primitives/PrimitiveNumber.h"

#include "core/OSQL/Castable.h"
#include "core/OSQL/DBBinary.h"
#include "core/OSQL/DBField.h"
#include "core/OSQL/DBValue.h"
#include "core/OSQL/DropTableQuery.h"
#include "core/OSQL/DialectString.h"
#include "core/OSQL/ExtractPart.h"
#include "core/OSQL/FieldTable.h"
#include "core/OSQL/FromTable.h"
#include "core/OSQL/FullText.h"
#include "core/OSQL/GroupBy.h"
// b0rked atm
// #include "core/OSQL/Joiner.h"
#include "core/OSQL/OrderBy.h"
#include "core/OSQL/SelectField.h"
#include "core/OSQL/SQLTableName.h"
#include "core/OSQL/Query.h"
#include "core/OSQL/QueryIdentification.h"
// commented out while SelectQuery is unimplemented here
// #include "core/OSQL/QuerySkeleton.h"

#include "core/Logic/LogicalObject.h"
#include "core/Logic/MappableObject.h"

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
	REGISTER_ONPHP_INTERFACE(Aliased);
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
	
	REGISTER_ONPHP_STD_CLASS(Singleton);
	ONPHP_CLASS_IS_ABSTRACT(Singleton);
	
	REGISTER_ONPHP_STD_CLASS(Ternary);
	REGISTER_ONPHP_PROPERTY(Ternary, "trinity", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_IMPLEMENTS(Ternary, Stringable);
	ONPHP_CLASS_IS_FINAL(Ternary);
	
	REGISTER_ONPHP_STD_CLASS(PlainForm);
	REGISTER_ONPHP_PROPERTY(PlainForm, "primitives", ZEND_ACC_PROTECTED);
	ONPHP_CLASS_IS_ABSTRACT(PlainForm);
	
	REGISTER_ONPHP_SUB_CLASS(RegulatedForm, PlainForm);
	REGISTER_ONPHP_PROPERTY(RegulatedForm, "rules", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_PROPERTY(RegulatedForm, "violated", ZEND_ACC_PROTECTED);
	ONPHP_CLASS_IS_ABSTRACT(RegulatedForm);
	
	REGISTER_ONPHP_SUB_CLASS(Form, RegulatedForm);
	REGISTER_ONPHP_CLASS_CONST_LONG(Form, "WRONG", 0x0001);
	REGISTER_ONPHP_CLASS_CONST_LONG(Form, "MISSING", 0x0002);
	REGISTER_ONPHP_PROPERTY(Form, "proto", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY(Form, "errors", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY(Form, "labels", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY(Form, "describedLabels", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY_BOOL(Form, "importFiltering", 1, ZEND_ACC_PRIVATE);
	ONPHP_CLASS_IS_FINAL(Form);
	
	REGISTER_ONPHP_STD_CLASS(FormField);
	REGISTER_ONPHP_PROPERTY(FormField, "primitiveName", ZEND_ACC_PRIVATE);
	ONPHP_CLASS_IS_FINAL(FormField);
	
	REGISTER_ONPHP_INTERFACE(Filtrator);
	
	REGISTER_ONPHP_SUB_CLASS(BaseFilter, Singleton);
	REGISTER_ONPHP_IMPLEMENTS(BaseFilter, Filtrator);
	REGISTER_ONPHP_IMPLEMENTS(BaseFilter, Instantiatable);
	ONPHP_CLASS_IS_ABSTRACT(BaseFilter);
	
	REGISTER_ONPHP_STD_CLASS(BasePrimitive);
	REGISTER_ONPHP_PROPERTY(BasePrimitive, "name", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_PROPERTY(BasePrimitive, "default", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_PROPERTY(BasePrimitive, "value", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_PROPERTY_BOOL(BasePrimitive, "required", 0, ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_PROPERTY_BOOL(BasePrimitive, "imported", 0, ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_PROPERTY(BasePrimitive, "raw", ZEND_ACC_PROTECTED);
	ONPHP_CLASS_IS_ABSTRACT(BasePrimitive);
	
	REGISTER_ONPHP_SUB_CLASS(RangedPrimitive, BasePrimitive);
	REGISTER_ONPHP_PROPERTY(RangedPrimitive, "min", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_PROPERTY(RangedPrimitive, "max", ZEND_ACC_PROTECTED);
	ONPHP_CLASS_IS_ABSTRACT(RangedPrimitive);
	
	REGISTER_ONPHP_SUB_CLASS(ComplexPrimitive, RangedPrimitive);
	REGISTER_ONPHP_PROPERTY(ComplexPrimitive, "single", ZEND_ACC_PRIVATE);
	ONPHP_CLASS_IS_ABSTRACT(ComplexPrimitive);
	
	REGISTER_ONPHP_INTERFACE(ListedPrimitive);
	
	REGISTER_ONPHP_SUB_CLASS(FiltrablePrimitive, RangedPrimitive);
	REGISTER_ONPHP_PROPERTY(FiltrablePrimitive, "importFilter", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY(FiltrablePrimitive, "displayFilter", ZEND_ACC_PRIVATE);
	ONPHP_CLASS_IS_ABSTRACT(FiltrablePrimitive);
	
	REGISTER_ONPHP_SUB_CLASS(PrimitiveNumber, FiltrablePrimitive);
	ONPHP_CLASS_IS_ABSTRACT(PrimitiveNumber);
	
	REGISTER_ONPHP_INTERFACE(LogicalObject);
	REGISTER_ONPHP_IMPLEMENTS(LogicalObject, DialectString);
	
	REGISTER_ONPHP_INTERFACE(MappableObject);
	REGISTER_ONPHP_IMPLEMENTS(MappableObject, DialectString);
	
	REGISTER_ONPHP_STD_CLASS(Identifier);
	REGISTER_ONPHP_PROPERTY(Identifier, "id", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY_BOOL(Identifier, "final", 0, ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_IMPLEMENTS(Identifier, Identifiable);
	ONPHP_CLASS_IS_FINAL(Identifier);
	
	REGISTER_ONPHP_STD_CLASS(IdentifiableObject);
	REGISTER_ONPHP_PROPERTY(IdentifiableObject, "id", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_IMPLEMENTS(IdentifiableObject, Identifiable);
	
	REGISTER_ONPHP_SUB_CLASS(NamedObject, IdentifiableObject);
	REGISTER_ONPHP_PROPERTY(NamedObject, "name", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_IMPLEMENTS(NamedObject, Named);
	ONPHP_CLASS_IS_ABSTRACT(NamedObject);
	
	REGISTER_ONPHP_SUB_CLASS(Enumeration, NamedObject);
	REGISTER_ONPHP_PROPERTY(Enumeration, "names", ZEND_ACC_PROTECTED);
	ONPHP_CLASS_IS_ABSTRACT(Enumeration);
	
	// skipping REGISTER_ONPHP_IMPLEMENTS
	zend_class_implements(onphp_ce_Enumeration TSRMLS_CC, 1, zend_ce_serializable);
	
	REGISTER_ONPHP_STD_CLASS(StaticFactory);
	ONPHP_CLASS_IS_ABSTRACT(StaticFactory);
	
	REGISTER_ONPHP_SUB_CLASS(Dialect, Singleton);
	REGISTER_ONPHP_IMPLEMENTS(Dialect, Instantiatable);
	ONPHP_CLASS_IS_ABSTRACT(Dialect);
	
	REGISTER_ONPHP_SUB_CLASS(ImaginaryDialect, Dialect);
	ONPHP_CLASS_IS_FINAL(ImaginaryDialect);
	
	REGISTER_ONPHP_STD_CLASS(Castable);
	REGISTER_ONPHP_PROPERTY(Castable, "cast", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_IMPLEMENTS(Castable, DialectString);
	ONPHP_CLASS_IS_ABSTRACT(Castable);
	
	REGISTER_ONPHP_SUB_CLASS(FieldTable, Castable);
	REGISTER_ONPHP_PROPERTY(FieldTable, "field", ZEND_ACC_PROTECTED);
	ONPHP_CLASS_IS_ABSTRACT(FieldTable);
	
	REGISTER_ONPHP_STD_CLASS(FromTable);
	REGISTER_ONPHP_PROPERTY(FromTable, "table", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY(FromTable, "alias", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY(FromTable, "schema", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_IMPLEMENTS(FromTable, Aliased);
	REGISTER_ONPHP_IMPLEMENTS(FromTable, SQLTableName);
	ONPHP_CLASS_IS_FINAL(FromTable);
	
	REGISTER_ONPHP_SUB_CLASS(DBValue, Castable);
	REGISTER_ONPHP_PROPERTY(DBValue, "value", ZEND_ACC_PRIVATE);
	
	REGISTER_ONPHP_SUB_CLASS(DBBinary, DBValue);
	ONPHP_CLASS_IS_FINAL(DBBinary);
	
	REGISTER_ONPHP_SUB_CLASS(DBField, Castable);
	REGISTER_ONPHP_PROPERTY(DBField, "field", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY(DBField, "table", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_IMPLEMENTS(DBField, SQLTableName);
	
	REGISTER_ONPHP_SUB_CLASS(DropTableQuery, QueryIdentification);
	REGISTER_ONPHP_PROPERTY(DropTableQuery, "name", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY_BOOL(DropTableQuery, "cascade", 0, ZEND_ACC_PRIVATE);
	ONPHP_CLASS_IS_FINAL(DropTableQuery);
	
	REGISTER_ONPHP_STD_CLASS(ExtractPart);
	REGISTER_ONPHP_PROPERTY(ExtractPart, "what", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY(ExtractPart, "from", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_IMPLEMENTS(ExtractPart, DialectString);
	REGISTER_ONPHP_IMPLEMENTS(ExtractPart, MappableObject);
	ONPHP_CLASS_IS_FINAL(ExtractPart);
	
	REGISTER_ONPHP_SUB_CLASS(SelectField, FieldTable);
	REGISTER_ONPHP_PROPERTY(SelectField, "alias", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_IMPLEMENTS(SelectField, Aliased);
	ONPHP_CLASS_IS_FINAL(SelectField);
	
	REGISTER_ONPHP_STD_CLASS(FullText);
	REGISTER_ONPHP_PROPERTY(FullText, "logic", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_PROPERTY(FullText, "field", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_PROPERTY(FullText, "words", ZEND_ACC_PROTECTED);
	REGISTER_ONPHP_IMPLEMENTS(FullText, DialectString);
	REGISTER_ONPHP_IMPLEMENTS(FullText, MappableObject);
	REGISTER_ONPHP_IMPLEMENTS(FullText, LogicalObject);
	ONPHP_CLASS_IS_ABSTRACT(FullText);
	
	REGISTER_ONPHP_SUB_CLASS(GroupBy, FieldTable);
	REGISTER_ONPHP_IMPLEMENTS(GroupBy, MappableObject);
	ONPHP_CLASS_IS_FINAL(GroupBy);
	
	// b0rked atm
	// REGISTER_ONPHP_STD_CLASS(Joiner);
	// REGISTER_ONPHP_PROPERTY(Joiner, "from", ZEND_ACC_PRIVATE);
	// REGISTER_ONPHP_PROPERTY(Joiner, "tables", ZEND_ACC_PRIVATE);
	// REGISTER_ONPHP_IMPLEMENTS(Joiner, DialectString);
	// ONPHP_CLASS_IS_FINAL(Joiner);
	
	REGISTER_ONPHP_SUB_CLASS(OrderBy, FieldTable);
	REGISTER_ONPHP_PROPERTY(OrderBy, "direction", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_IMPLEMENTS(OrderBy, MappableObject);
	ONPHP_CLASS_IS_FINAL(OrderBy);
	
	REGISTER_ONPHP_STD_CLASS(QueryIdentification);
	REGISTER_ONPHP_IMPLEMENTS(QueryIdentification, Query);
	ONPHP_CLASS_IS_ABSTRACT(QueryIdentification);
	
	// commented out while SelectQuery is unimplemented here
	// REGISTER_ONPHP_SUB_CLASS(QuerySkeleton, QueryIdentification);
	// REGISTER_ONPHP_PROPERTY(QuerySkeleton, "where", ZEND_ACC_PROTECTED);
	// REGISTER_ONPHP_PROPERTY(QuerySkeleton, "whereLogic", ZEND_ACC_PROTECTED);
	// ONPHP_CLASS_IS_ABSTRACT(QuerySkeleton);
	
	return PHP_MINIT(Exceptions)(INIT_FUNC_ARGS_PASSTHRU);
}
