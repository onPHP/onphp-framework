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

#include "core/Exceptions.h"

#include "core/Form/PlainForm.h"

#include "core/Logic/LogicalObject.h"

ONPHP_METHOD(RegulatedForm, __construct)
{
	ONPHP_CONSTRUCT_ARRAY(rules);
	ONPHP_CONSTRUCT_ARRAY(violated);
	
	zend_call_method_with_0_params(
		&getThis(),
		onphp_ce_PlainForm,
		NULL,
		"__construct",
		NULL
	);
}

ONPHP_METHOD(RegulatedForm, addRule)
{
	char *name;
	unsigned int length;
	zval
		*rule,
		*rules = ONPHP_READ_PROPERTY(getThis(), "rules");
	
	ONPHP_GET_ARGS("sO", &name, &length, &rule, onphp_ce_LogicalObject);
	
	ONPHP_ASSOC_SET(rules, name, rule);
	
	RETURN_THIS;
}

ONPHP_METHOD(RegulatedForm, dropRuleByName)
{
	char *name;
	unsigned int length;
	zval *rules = ONPHP_READ_PROPERTY(getThis(), "rules");
	
	ONPHP_GET_ARGS("s", &name, &length);
	
	if (!ONPHP_ASSOC_ISSET(rules, name)) {
		ONPHP_THROW(
			MissingElementException,
			"no such rule with '%s' name",
			name
		);
	}
	
	ONPHP_ASSOC_UNSET(rules, name);
	
	RETURN_THIS;
}

ONPHP_METHOD(RegulatedForm, checkRules)
{
	zval
		*result,
		*logicalObject,
		*self = getThis(),
		*rules = ONPHP_READ_PROPERTY(getThis(), "rules"),
		*violated = ONPHP_READ_PROPERTY(getThis(), "violated");
	
	ONPHP_FOREACH(rules, logicalObject) {
		ONPHP_CALL_METHOD_1(logicalObject, "toboolean", &result, self);
		
		if (!ONPHP_CHECK_EMPTY(result)) {
			char *key;
			ulong length;
			unsigned int type;
			
			zval_ptr_dtor(&result);
			
			type =
				zend_hash_get_current_key(
					Z_ARRVAL_P(rules),
					&key,
					&length,
					0
				);
			
			if (type != HASH_KEY_IS_STRING) {
				ONPHP_THROW(WrongStateException, "weird key found");
			}
			
			// Form::WRONG == 1
			ONPHP_ASSOC_SET_LONG(violated, key, 1);
		} else {
			zval_ptr_dtor(&result);
		}
	}
	
	RETURN_THIS;
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_ONE_AND_LOGICAL_OBJECT;

zend_function_entry onphp_funcs_RegulatedForm[] = {
	ONPHP_ME(RegulatedForm, __construct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(RegulatedForm, addRule, arginfo_one_and_logical_object, ZEND_ACC_PUBLIC)
	ONPHP_ME(RegulatedForm, dropRuleByName, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(RegulatedForm, checkRules, NULL, ZEND_ACC_PUBLIC)
};
