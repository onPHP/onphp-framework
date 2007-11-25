/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp.h"

#include "core/Form/Form.h"
#include "core/Form/RegulatedForm.h"
#include "core/Form/FormField.h"

#include "core/Form/Primitives/BasePrimitive.h"
#include "core/Form/Primitives/FiltrablePrimitive.h"

#include "core/Logic/LogicalObject.h"

#include "core/Exceptions.h"

ONPHP_METHOD(Form, __construct)
{
	ONPHP_CONSTRUCT_ARRAY(errors);
	ONPHP_CONSTRUCT_ARRAY(labels);
	ONPHP_CONSTRUCT_ARRAY(describedLabels);
	
	ONPHP_CALL_PARENT_0(getThis(), "__construct", NULL);
}

ONPHP_METHOD(Form, create)
{
	zval *object;
	
	ONPHP_MAKE_OBJECT(Form, object);
	
	ONPHP_CALL_METHOD_0_NORET(object, "__construct", NULL);
	
	if (EG(exception)) {
		ZVAL_FREE(object);
		return;
	}
	
	RETURN_ZVAL(object, 1, 1);
}

ONPHP_METHOD(Form, getErrors)
{
	zval
		*out,
		*errors = ONPHP_READ_PROPERTY(getThis(), "errors"),
		*violated = ONPHP_READ_PROPERTY(getThis(), "violated");
	
	zend_call_method_with_2_params(
		NULL,
		NULL,
		NULL,
		"array_merge",
		&out,
		errors,
		violated
	);
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Form, dropAllErrors)
{
	zend_hash_clean(Z_ARRVAL_P(ONPHP_READ_PROPERTY(getThis(), "errors")));
	zend_hash_clean(Z_ARRVAL_P(ONPHP_READ_PROPERTY(getThis(), "violated")));
	
	RETURN_THIS;
}

ONPHP_METHOD(Form, enableImportFiltering)
{
	ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "importFiltering", 1);
	
	RETURN_THIS;
}

ONPHP_METHOD(Form, disableImportFiltering)
{
	ONPHP_UPDATE_PROPERTY_BOOL(getThis(), "importFiltering", 0);
	
	RETURN_THIS;
}

ONPHP_METHOD(Form, markMissing)
{
	zval *name, *mark;
	
	ONPHP_GET_ARGS("z", &name);
	
	ALLOC_INIT_ZVAL(mark);
	ZVAL_LONG(mark, 2); // self::MISSING
	
	ONPHP_CALL_METHOD_2_NORET(getThis(), "markcustom", NULL, name, mark);
	
	zval_ptr_dtor(&mark);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_THIS;
}

ONPHP_METHOD(Form, markWrong)
{
	char *name;
	unsigned int length;
	zval
		*rules = ONPHP_READ_PROPERTY(getThis(), "rules"),
		*errors = ONPHP_READ_PROPERTY(getThis(), "errors"),
		*violated = ONPHP_READ_PROPERTY(getThis(), "violated"),
		*primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_GET_ARGS("s", &name, &length);
	
	if (ONPHP_ASSOC_ISSET(primitives, name)) {
		ONPHP_ASSOC_SET_LONG(errors, name, 1); // self::WRONG
	} else if (ONPHP_ASSOC_ISSET(rules, name)) {
		ONPHP_ASSOC_SET_LONG(violated, name, 1); // self::WRONG
	} else {
		ONPHP_THROW(
			MissingElementException,
			"'%s' does not match known primitives or rules",
			name
		);
	}
	
	RETURN_THIS;
}

ONPHP_METHOD(Form, markGood)
{
	char *name;
	unsigned int length;
	zval
		*rules = ONPHP_READ_PROPERTY(getThis(), "rules"),
		*errors = ONPHP_READ_PROPERTY(getThis(), "errors"),
		*violated = ONPHP_READ_PROPERTY(getThis(), "violated"),
		*primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_GET_ARGS("s", &name, &length);
	
	if (ONPHP_ASSOC_ISSET(primitives, name)) {
		ONPHP_ASSOC_UNSET(errors, name);
	} else if (ONPHP_ASSOC_ISSET(rules, name)) {
		ONPHP_ASSOC_UNSET(violated, name);
	} else {
		ONPHP_THROW(
			MissingElementException,
			"'%s' does not match known primitives or rules",
			name
		);
	}
}

ONPHP_METHOD(Form, markCustom)
{
	unsigned int length, mark;
	char *name;
	zval *primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_GET_ARGS("sl", &name, &length, &mark);
	
	if (!ONPHP_ASSOC_ISSET(primitives, name)) {
		ONPHP_THROW(
			MissingElementException,
			"'%s' does not match known primitives or rules",
			name
		);
	}
	
	ONPHP_ASSOC_SET_LONG(primitives, name, mark);
	
	RETURN_THIS;
}

ONPHP_METHOD(Form, getTextualErrors)
{
	zval
		**name,
		*list,
		*label,
		*labels = ONPHP_READ_PROPERTY(getThis(), "labels");
	
	ONPHP_MAKE_ARRAY(list);
	
	ONPHP_FOREACH(labels, name) {
		char *key;
		ulong length;
		unsigned int result;
		zval *keyName;
		
		result =
			zend_hash_get_current_key(
				Z_ARRVAL_P(labels),
				&key,
				&length,
				0
			);
		
		if (result != HASH_KEY_IS_STRING) {
			ZVAL_FREE(list);
			ONPHP_THROW(
				WrongStateException,
				"weird key found"
			);
		}
		
		ALLOC_INIT_ZVAL(keyName);
		ZVAL_STRINGL(keyName, key, length, 0);
		
		ONPHP_CALL_METHOD_1_NORET(
			getThis(),
			"gettextualerrorfor",
			&label,
			keyName
		);
		
		ZVAL_FREE(keyName);
		
		if (EG(exception)) {
			ZVAL_FREE(list);
			return;
		}
		
		ONPHP_ARRAY_ADD(list, label);
		
		zval_ptr_dtor(&label);
	}
	
	RETURN_ZVAL(list, 1, 1);
}

#define ONPHP_FORM_LABEL_GETTER(method_name, property)				\
ONPHP_METHOD(Form, method_name)										\
{																	\
	char *name;														\
	unsigned int length;											\
	zval															\
		*out,														\
		*item = NULL,												\
		*subList,													\
		*errors = ONPHP_READ_PROPERTY(getThis(), "errors"),			\
		*property = ONPHP_READ_PROPERTY(getThis(), # property),		\
		*violated = ONPHP_READ_PROPERTY(getThis(), "violated");		\
																	\
	ONPHP_GET_ARGS("s", &name, &length);							\
																	\
	if (ONPHP_ASSOC_ISSET(property, name)) {						\
		ONPHP_ASSOC_GET(property, name, subList);					\
																	\
		if (														\
			(Z_TYPE_P(subList) == IS_ARRAY)							\
			&& ONPHP_ASSOC_ISSET(violated, name)					\
		) {															\
			ONPHP_ASSOC_GET(violated, name, item);					\
		} else if (													\
			ONPHP_ASSOC_ISSET(errors, name)							\
		) {															\
			ONPHP_ASSOC_GET(errors, name, item);					\
		}															\
																	\
		if (item && (Z_TYPE_P(item) == IS_STRING)) {				\
			if (ONPHP_ASSOC_ISSET(subList, Z_STRVAL_P(item))) {		\
				ONPHP_ASSOC_GET(subList, Z_STRVAL_P(item), out);	\
																	\
				RETURN_ZVAL(out, 1, 0);								\
			}														\
		}															\
	}																\
																	\
	RETURN_NULL();													\
}

ONPHP_FORM_LABEL_GETTER(getTextualErrorFor, labels);
ONPHP_FORM_LABEL_GETTER(getErrorDescriptionFor, describedLabels);

#undef ONPHP_FORM_LABEL_GETTER

ONPHP_METHOD(Form, addErrorDescription)
{
	char *name, *description;
	unsigned int nameLength, type, descriptionLength;
	zval
		*subList,
		*rules = ONPHP_READ_PROPERTY(getThis(), "rules"),
		*primitives = ONPHP_READ_PROPERTY(getThis(), "primitives"),
		*describedLabels = ONPHP_READ_PROPERTY(getThis(), "describedLabels");
	
	ONPHP_GET_ARGS(
		"sls",
		&name,
		&nameLength,
		&type,
		&description,
		&descriptionLength
	);
	
	if (
		!ONPHP_ASSOC_ISSET(primitives, name)
		&& !ONPHP_ASSOC_ISSET(rules, name)
	) {
		ONPHP_THROW(
			MissingElementException,
			"'%s' does not match known primitives or rules",
			name
		);
	}
	
	if (!ONPHP_ASSOC_ISSET(describedLabels, name)) {
		ONPHP_MAKE_ARRAY(subList);
		ONPHP_ASSOC_SET(describedLabels, name, subList);
		zval_ptr_dtor(&subList);
		subList = NULL;
	}
	
	ONPHP_ASSOC_GET(describedLabels, name, subList);
	
	add_index_stringl(subList, type, description, descriptionLength, 1);
	
	RETURN_THIS;
}

#define ONPHP_FORM_ADD_TYPED_LABEL(method_name, value)	\
ONPHP_METHOD(Form, method_name)							\
{														\
	zval *name, *label, *type;							\
														\
	ONPHP_GET_ARGS("zz", &name, &label);				\
														\
	ALLOC_INIT_ZVAL(type);								\
	ZVAL_LONG(type, value);								\
														\
	ONPHP_CALL_METHOD_3_NORET(							\
		getThis(),										\
		"adderrorlabel",								\
		NULL,											\
		name,											\
		type,											\
		label											\
	);													\
														\
	ZVAL_FREE(type);									\
														\
	if (EG(exception)) {								\
		return;											\
	}													\
														\
	RETURN_THIS;										\
}

ONPHP_FORM_ADD_TYPED_LABEL(addWrongLabel, 1);	// self::WRONG
ONPHP_FORM_ADD_TYPED_LABEL(addMissingLabel, 2);	// self::MISSING

#undef ONPHP_FORM_ADD_TYPED_LABEL

ONPHP_METHOD(Form, addCustomLabel)
{
	zval *name, *type, *label;
	
	ONPHP_GET_ARGS("zzz", &name, &type, &label);
	
	ONPHP_CALL_METHOD_3(
		getThis(),
		"adderrorlabel",
		NULL,
		name,
		type,
		label
	);
	
	RETURN_THIS;
}

ONPHP_METHOD(Form, import)
{
	zval
		**prm,
		*scope,
		*primitives;
	
	ONPHP_GET_ARGS("a", &scope);
	
	primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_FOREACH(primitives, prm) {
		ONPHP_CALL_METHOD_2(getThis(), "importprimitive", NULL, scope, *prm);
	}
	
	RETURN_THIS;
}

ONPHP_METHOD(Form, importMore)
{
	zval
		**prm,
		*scope,
		*primitives;
	
	ONPHP_GET_ARGS("a", &scope);
	
	primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_FOREACH(primitives, prm) {
		zval *imported;
		
		ONPHP_CALL_METHOD_0(*prm, "isimported", &imported);
		
		if (
			(Z_TYPE_P(imported) != IS_BOOL)
			|| !zval_is_true(imported)
		) {
			ONPHP_CALL_METHOD_2_NORET(
				getThis(),
				"importprimitive",
				NULL,
				scope,
				*prm
			);
		}
		
		zval_ptr_dtor(&imported);
		
		if (EG(exception)) {
			return;
		}
	}
	
	RETURN_THIS;
}

ONPHP_METHOD(Form, importOne)
{
	zval
		*out,
		*name,
		*scope,
		*primitive;
	
	ONPHP_GET_ARGS("za", &name, &scope);
	
	ONPHP_CALL_METHOD_1(getThis(), "get", &primitive, name);
	
	ONPHP_CALL_METHOD_2_NORET(
		getThis(),
		"importprimitive",
		&out,
		scope,
		primitive
	);
	
	zval_ptr_dtor(&primitive);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Form, importValue)
{
	zval
		*out,
		*name,
		*value,
		*result,
		*primitive;
	
	ONPHP_GET_ARGS("zz", &name, &value);
	
	ONPHP_CALL_METHOD_1(getThis(), "get", &primitive, name);
	
	ONPHP_CALL_METHOD_1_NORET(primitive, "importvalue", &out, value);
	
	if (EG(exception)) {
		zval_ptr_dtor(&primitive);
		return;
	}
	
	ONPHP_CALL_METHOD_2_NORET(
		getThis(),
		"checkimportresult",
		&result,
		primitive,
		out
	);
	
	zval_ptr_dtor(&primitive);
	zval_ptr_dtor(&out);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(result, 1, 1);
}

ONPHP_METHOD(Form, importOneMore)
{
	zval
		*name,
		*scope,
		*result,
		*imported,
		*primitive;
	
	ONPHP_GET_ARGS("za", &name, &scope);
	
	ONPHP_CALL_METHOD_1(getThis(), "get", &primitive, name);
	
	ONPHP_CALL_METHOD_0_NORET(primitive, "isimported", &imported);
	
	if (EG(exception)) {
		zval_ptr_dtor(&primitive);
		return;
	}
	
	if (
		(Z_TYPE_P(imported) != IS_BOOL)
		|| !zval_is_true(imported)
	) {
		ONPHP_CALL_METHOD_2_NORET(
			getThis(),
			"importprimitive",
			&result,
			scope,
			primitive
		);
		
		if (!EG(exception)) {
			RETVAL_ZVAL(result, 1, 1);
		}
	} else {
		RETVAL_THIS;
	}
	
	zval_ptr_dtor(&primitive);
	zval_ptr_dtor(&imported);
}

ONPHP_METHOD(Form, exportValue)
{
	zval *name, *primitive, *out;
	
	ONPHP_GET_ARGS("z", &name);
	
	ONPHP_CALL_METHOD_1(getThis(), "get", &primitive, name);
	
	ONPHP_CALL_METHOD_0_NORET(primitive, "exportvalue", &out);
	
	zval_ptr_dtor(&primitive);
	
	if (EG(exception)) {
		return;
	}
	
	RETURN_ZVAL(out, 1, 1);
}

ONPHP_METHOD(Form, export)
{
	zval
		**prm,
		*list,
		*primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	ONPHP_MAKE_ARRAY(list);
	
	ONPHP_FOREACH(primitives, prm) {
		zval *imported;
		
		ONPHP_CALL_METHOD_0_NORET(*prm, "isimported", &imported);
		
		if (EG(exception)) {
			ZVAL_FREE(list);
			return;
		}
		
		if (
			(Z_TYPE_P(imported) != IS_BOOL)
			|| !zval_is_true(imported)
		) {
			zval *value, *name;
			
			ONPHP_CALL_METHOD_0_NORET(*prm, "exportvalue", &value);
			
			if (EG(exception)) {
				ZVAL_FREE(list);
				zval_ptr_dtor(&imported);
				return;
			}
			
			ONPHP_CALL_METHOD_0_NORET(*prm, "getname", &name);
			
			if (EG(exception)) {
				ZVAL_FREE(list);
				zval_ptr_dtor(&imported);
				zval_ptr_dtor(&value);
				return;
			}
			
			ONPHP_ASSOC_SET(list, Z_STRVAL_P(name), value);
			
			zval_ptr_dtor(&name);
		}
		
		zval_ptr_dtor(&imported);
	}
	
	RETURN_ZVAL(list, 1, 1);
}

ONPHP_METHOD(Form, toFormValue)
{
	zval *value, *out;
	
	ONPHP_GET_ARGS("z", &value);
	
	if (ONPHP_INSTANCEOF(value, FormField)) {
		zval *name;
		
		ONPHP_CALL_METHOD_0(value, "getname", &name);
		
		ONPHP_CALL_METHOD_1_NORET(getThis(), "getvalue", &out, name);
		
		zval_ptr_dtor(&name);
		
		if (EG(exception)) {
			return;
		}
		
		RETURN_ZVAL(out, 1, 1);
	} else if (ONPHP_INSTANCEOF(value, LogicalObject)) {
		ONPHP_CALL_METHOD_1(value, "toboolean", &out, getThis());
		
		RETURN_ZVAL(out, 1, 1);
	} else {
		RETURN_ZVAL(value, 1, 0);
	}
}

ONPHP_SETTER(Form, setProto, proto);
ONPHP_GETTER(Form, getProto, proto);

ONPHP_METHOD(Form, importPrimitive)
{
	zval
		*prm,
		*scope,
		*importFiltering = ONPHP_READ_PROPERTY(getThis(), "importFiltering");
	
	ONPHP_GET_ARGS("aO", &scope, &prm, onphp_ce_BasePrimitive);
	
	if (!zval_is_true(importFiltering)) {
		if (ONPHP_INSTANCEOF(prm, FiltrablePrimitive)) {
			zval *chain, *result, *out;
			
			ONPHP_CALL_METHOD_0(prm, "getimportfilter", &chain);
			
			ONPHP_CALL_METHOD_0_NORET(prm, "dropimportfilters", NULL);
			
			if (EG(exception)) {
				zval_ptr_dtor(&chain);
				return;
			}
			
			ONPHP_CALL_METHOD_1_NORET(prm, "import", &result, scope);
			
			if (EG(exception)) {
				zval_ptr_dtor(&chain);
				return;
			}
			
			ONPHP_CALL_METHOD_2_NORET(
				getThis(),
				"checkimportresult",
				&out,
				prm,
				result
			);
			
			zval_ptr_dtor(&result);
			
			if (EG(exception)) {
				zval_ptr_dtor(&chain);
				return;
			}
			
			ONPHP_CALL_METHOD_1_NORET(prm, "setimportfilter", NULL, chain);
			
			zval_ptr_dtor(&chain);
			
			if (EG(exception)) {
				zval_ptr_dtor(&out);
				return;
			}
			
			RETURN_ZVAL(out, 1, 1);
		} else {
			zend_class_entry **cep;
			
			ONPHP_FIND_FOREIGN_CLASS("PrimitiveForm", cep);
			
			if (instanceof_function(Z_OBJCE_P(prm), *cep TSRMLS_CC)) {
				zval *result, *unfiltered;
				
				ONPHP_CALL_METHOD_1(prm, "unfilteredimport", &unfiltered, scope);
				
				ONPHP_CALL_METHOD_2_NORET(
					getThis(),
					"checkimportresult",
					&result,
					prm,
					unfiltered
				);
				
				zval_ptr_dtor(&unfiltered);
				
				if (EG(exception)) {
					return;
				}
				
				RETURN_ZVAL(result, 1, 1);
			}
		}
	} else {
		zval *result, *out;
		
		ONPHP_CALL_METHOD_1(prm, "import", &out, scope);
		
		ONPHP_CALL_METHOD_2_NORET(
			getThis(),
			"checkimportresult",
			&result,
			prm,
			out
		);
		
		zval_ptr_dtor(&out);
		
		if (EG(exception)) {
			return;
		}
		
		RETURN_ZVAL(result, 1, 1);
	}
}

ONPHP_METHOD(Form, checkImportResult)
{
	zval
		*prm,
		*name,
		*errors,
		*result;
	
	ONPHP_GET_ARGS("Oz", &prm, onphp_ce_BasePrimitive, &result);
	
	ONPHP_CALL_METHOD_0(prm, "getname", &name);
	
	errors = ONPHP_READ_PROPERTY(getThis(), "errors");
	
	if (Z_TYPE_P(result) == IS_NULL) {
		zval *required;
		
		ONPHP_CALL_METHOD_0_NORET(prm, "isrequired", &required);
		
		if (EG(exception)) {
			zval_ptr_dtor(&name);
			return;
		}
		
		if (
			(Z_TYPE_P(required) == IS_BOOL)
			&& zval_is_true(required)
		) {
			ONPHP_ASSOC_SET_LONG(errors, Z_STRVAL_P(name), 2); // self::MISSING
		}
		
		zval_ptr_dtor(&required);
	} else if (Z_TYPE_P(result) == IS_BOOL) {
		if (zval_is_true(result)) {
			ONPHP_ASSOC_UNSET(errors, Z_STRVAL_P(name));
		} else {
			ONPHP_ASSOC_SET_LONG(errors, Z_STRVAL_P(name), 1); // self::WRONG
		}
	}
	
	zval_ptr_dtor(&name);
	
	RETURN_THIS;
}

ONPHP_METHOD(Form, addErrorLabel)
{
	char *name, *label;
	unsigned int nameLength, type, labelLength;
	zval *rules, *labels, *primitives, *subList;
	
	ONPHP_GET_ARGS("sls", &name, &nameLength, &type, &label, &labelLength);
	
	rules = ONPHP_READ_PROPERTY(getThis(), "rules");
	primitives = ONPHP_READ_PROPERTY(getThis(), "primitives");
	
	if (
		!ONPHP_ASSOC_ISSET(primitives, name)
		&& !ONPHP_ASSOC_ISSET(rules, name)
	) {
		ONPHP_THROW(
			MissingElementException,
			"'%s' does not match known primitives or rules",
			name
		);
	}
	
	labels = ONPHP_READ_PROPERTY(getThis(), "labels");
	
	if (!ONPHP_ASSOC_ISSET(labels, name)) {
		ONPHP_MAKE_ARRAY(subList);
		ONPHP_ASSOC_SET(labels, name, subList);
		zval_ptr_dtor(&subList);
		subList = NULL;
	}
	
	ONPHP_ASSOC_GET(labels, name, subList);
	
	add_index_stringl(subList, type, label, labelLength, 1);
	
	RETURN_THIS;
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_TWO;
static ONPHP_ARGINFO_THREE;
static ONPHP_ARGINFO_ONE_AND_BASE_PRIMITIVE;
static
	ZEND_BEGIN_ARG_INFO(arginfo_dto_proto, 0)
		ZEND_ARG_OBJ_INFO(0, dto_proto, DTOProto, 0)
	ZEND_END_ARG_INFO();
static
	ZEND_BEGIN_ARG_INFO(arginfo_base_primitive_and_one, 0)
		ZEND_ARG_OBJ_INFO(0, prm, BasePrimitive, 0)
		ZEND_ARG_INFO(0, first)
	ZEND_END_ARG_INFO()

zend_function_entry onphp_funcs_Form[] = {
	ONPHP_ME(Form, __construct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(Form, create, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Form, getErrors, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, dropAllErrors, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, enableImportFiltering, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, disableImportFiltering, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, markMissing, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, markWrong, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, markGood, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, markCustom, arginfo_two, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, getTextualErrors, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, getTextualErrorFor, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, getErrorDescriptionFor, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, addErrorDescription, arginfo_three, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, addWrongLabel, arginfo_two, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, addMissingLabel, arginfo_two, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, addCustomLabel, arginfo_three, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, import, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, importMore, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, importOne, arginfo_two, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, importValue, arginfo_two, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, importOneMore, arginfo_two, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, exportValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, export, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, toFormValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, setProto, arginfo_dto_proto, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, getProto, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(Form, importPrimitive, arginfo_one_and_base_primitive, ZEND_ACC_PRIVATE)
	ONPHP_ME(Form, checkImportResult, arginfo_base_primitive_and_one, ZEND_ACC_PRIVATE)
	ONPHP_ME(Form, addErrorLabel, arginfo_three, ZEND_ACC_PRIVATE)
	{NULL, NULL, NULL}
};
