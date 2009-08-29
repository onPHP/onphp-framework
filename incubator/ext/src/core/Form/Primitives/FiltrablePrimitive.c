/***************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp.h"

#include "core/Exceptions.h"

#include "core/Form/Filters/Filtrator.h"

#include "core/Form/Primitives/BasePrimitive.h"
#include "core/Form/Primitives/FiltrablePrimitive.h"

ONPHP_METHOD(FiltrablePrimitive, __construct)
{
	zval *name, *importFilter, *displayFilter;
	
	ONPHP_GET_ARGS("z", &name);
	
	ONPHP_MAKE_FOREIGN_OBJECT("FilterChain", importFilter);
	
	ALLOC_INIT_ZVAL(displayFilter);
	ZVAL_ZVAL(displayFilter, importFilter, 1, 0);
	
	ONPHP_UPDATE_PROPERTY(getThis(), "importFilter", importFilter);
	ONPHP_UPDATE_PROPERTY(getThis(), "displayFilter", displayFilter);
	
	zend_call_method_with_1_params(
		&getThis(),
		onphp_ce_BasePrimitive,
		&onphp_ce_BasePrimitive->constructor,
		"__construct",
		NULL,
		name
	);
	
	zval_ptr_dtor(&importFilter);
	zval_ptr_dtor(&displayFilter);
}

ONPHP_GETTER(FiltrablePrimitive, getDisplayFilter, displayFilter);
ONPHP_GETTER(FiltrablePrimitive, getImportFilter, importFilter);

ONPHP_SETTER(FiltrablePrimitive, setDisplayFilter, displayFilter);
ONPHP_SETTER(FiltrablePrimitive, setImportFilter, importFilter);

#define ONPHP_FILTRABLE_PRIMITIVE_ADD_FILTER(method_name, property_name)	\
ONPHP_METHOD(FiltrablePrimitive, method_name)								\
{																			\
	zval																	\
		*filter,															\
		*chain = ONPHP_READ_PROPERTY(getThis(), # property_name);			\
																			\
	ONPHP_GET_ARGS("O", &filter, onphp_ce_Filtrator);						\
																			\
	ONPHP_CALL_METHOD_1(chain, "add", NULL, filter);						\
																			\
	RETURN_THIS;															\
}

ONPHP_FILTRABLE_PRIMITIVE_ADD_FILTER(addDisplayFilter, displayFilter);
ONPHP_FILTRABLE_PRIMITIVE_ADD_FILTER(addImportFilter, importFilter);

#undef ONPHP_FILTRABLE_PRIMITIVE_ADD_FILTER

#define ONPHP_FILTRABLE_PRIMITIVE_CHAIN_DROP(method_name, property_name)	\
ONPHP_METHOD(FiltrablePrimitive, method_name)								\
{																			\
	zval *chain;															\
																			\
	chain = ONPHP_READ_PROPERTY(getThis(), # property_name);				\
																			\
	ZVAL_FREE(chain);														\
																			\
	ONPHP_MAKE_FOREIGN_OBJECT("FilterChain", chain);						\
																			\
	ONPHP_UPDATE_PROPERTY(getThis(), # property_name, chain);				\
}

ONPHP_FILTRABLE_PRIMITIVE_CHAIN_DROP(dropDisplayFilters, displayFilter);
ONPHP_FILTRABLE_PRIMITIVE_CHAIN_DROP(dropImportFilters, importFilter);

#undef ONPHP_FILTRABLE_PRIMITIVE_CHAIN_DROP

#define ONPHP_FILTRABLE_PRIMITIVE_APPLY_FILTERS								\
	if (Z_TYPE_P(value) == IS_ARRAY) {										\
		zval **element;														\
																			\
		ONPHP_FOREACH(value, element) {										\
			ONPHP_CALL_METHOD_1(chain, "apply", &filtered, *element);		\
			zval_ptr_dtor(element);											\
			*element = filtered;											\
		}																	\
		filtered = value;													\
	} else {																\
		ONPHP_CALL_METHOD_1(chain, "apply", &filtered, value);				\
	}

ONPHP_METHOD(FiltrablePrimitive, getDisplayValue)
{
	zval
		*value,
		*filtered,
		*chain = ONPHP_READ_PROPERTY(getThis(), "displayFilter");
	
	unsigned char is_array = (Z_TYPE_P(value) == IS_ARRAY);
	
	ONPHP_CALL_METHOD_0(getThis(), "getvalue", &value);
	
	ONPHP_FILTRABLE_PRIMITIVE_APPLY_FILTERS;
	
	if (!is_array) {
		zval_ptr_dtor(&value);
	}
	
	RETURN_ZVAL(filtered, 1, 1);
}

ONPHP_METHOD(FiltrablePrimitive, selfFilter)
{
	zval
		*value = ONPHP_READ_PROPERTY(getThis(), "value"),
		*chain = ONPHP_READ_PROPERTY(getThis(), "importFilter"),
		*filtered;
	
	unsigned char is_array = (Z_TYPE_P(value) == IS_ARRAY);
	
	ONPHP_FILTRABLE_PRIMITIVE_APPLY_FILTERS;
	
	ONPHP_UPDATE_PROPERTY(getThis(), "value", filtered);
	
	if (!is_array) {
		zval_ptr_dtor(&filtered);
	}
	
	RETURN_THIS;
}

#undef ONPHP_FILTRABLE_PRIMITIVE_APPLY_FILTERS

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_FILTRATOR;
static ONPHP_ARGINFO_FILTER_CHAIN;

zend_function_entry onphp_funcs_FiltrablePrimitive[] = {
	ONPHP_ME(FiltrablePrimitive, __construct, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(FiltrablePrimitive, setDisplayFilter, arginfo_filter_chain, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, addDisplayFilter, arginfo_filtrator, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, dropDisplayFilters, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, dropImportFilters, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, getDisplayValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, setImportFilter, arginfo_filter_chain, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, addImportFilter, arginfo_filtrator, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, getDisplayFilter, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, getImportFilter, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, selfFilter, NULL, ZEND_ACC_PROTECTED)
	{NULL, NULL, NULL}
};
