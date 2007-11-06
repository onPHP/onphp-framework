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
		&onphp_ce_PlainForm->constructor,
		"__construct",
		NULL
	);
}

ONPHP_METHOD(RegulatedForm, __destruct)
{
	ONPHP_PROPERTY_DESTRUCT(rules);
	ONPHP_PROPERTY_DESTRUCT(violated);
}

ONPHP_METHOD(RegulatedForm, addRule)
{
	zval
		*name,
		*rule,
		*rules = ONPHP_READ_PROPERTY(getThis(), "rules");
	
	ONPHP_GET_ARGS("zz", &name, &rule);
	
	if (Z_TYPE_P(name) != IS_STRING) {
		zend_throw_exception_ex(
			onphp_ce_WrongArgumentException,
			0 TSRMLS_CC,
			NULL
		);
		return;
	}
	
	ONPHP_ASSOC_SET(rules, name, rule);
	
	RETURN_THIS;
}

ONPHP_METHOD(RegulatedForm, dropRuleByName)
{
	zval *name, *rules = ONPHP_READ_PROPERTY(getThis(), "rules");
	
	ONPHP_GET_ARGS("z", &name);
	
	if (!ONPHP_ASSOC_ISSET(rules, name)) {
		zend_throw_exception_ex(
			onphp_ce_MissingElementException,
			0 TSRMLS_CC,
			NULL
		);
		return;
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
			
			type =
				zend_hash_get_current_key(
					Z_ARRVAL_P(rules),
					&key,
					&length,
					0
				);
			
			if (type != HASH_KEY_IS_STRING) {
				zend_throw_exception_ex(
					onphp_ce_WrongStateException,
					0 TSRMLS_CC,
					"weird key found"
				);
				return;
			}
			
			// Form::WRONG == 1
			add_assoc_long(violated, key, 1);
		}
	}
	
	RETURN_THIS;
}

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_ONE_AND_LOGICAL_OBJECT;

zend_function_entry onphp_funcs_RegulatedForm[] = {
	ONPHP_ME(RegulatedForm, __construct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(RegulatedForm, __destruct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_DTOR)
	ONPHP_ME(RegulatedForm, addRule, arginfo_one_and_logical_object, ZEND_ACC_PUBLIC)
	ONPHP_ME(RegulatedForm, dropRuleByName, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(RegulatedForm, checkRules, NULL, ZEND_ACC_PUBLIC)
};
