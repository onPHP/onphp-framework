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

#include "core/Form/Form.h"

#include "core/Logic/MappableObject.h"
#include "core/Logic/LogicalObject.h"

#include "core/OSQL/DBField.h"

void onphp_full_text_sanity_check(zval *field, zval *words TSRMLS_DC)
{
	if (
		!(
			(Z_TYPE_P(field) == IS_STRING)
			|| (ONPHP_INSTANCEOF(field, DBField))
		) || (
			!(Z_TYPE_P(words) == IS_ARRAY)
		)
	) {
		ONPHP_THROW_NORET(WrongArgumentException, NULL);
	}
}

#define ONPHP_FULL_TEXT_MASS_UPDATE(field, words, logic)	\
	ONPHP_UPDATE_PROPERTY(getThis(), "field", field);		\
	ONPHP_UPDATE_PROPERTY(getThis(), "words", words);		\
	ONPHP_UPDATE_PROPERTY(getThis(), "logic", logic);

ONPHP_METHOD(FullText, __construct)
{
	zval *field, *words, *logic;
	
	ONPHP_GET_ARGS("zaz", &field, &words, &logic);
	
	onphp_full_text_sanity_check(field, words TSRMLS_CC);
	
	if (EG(exception)) {
		return;
	}
	
	ONPHP_FULL_TEXT_MASS_UPDATE(field, words, logic);
}

ONPHP_METHOD(FullText, toMapped)
{
	zval *self, *field, *words, *logic, *atom, *dao, *query;
	
	ONPHP_GET_ARGS("oo", &dao, &query);
	
	ALLOC_INIT_ZVAL(self);
	self->value.obj = onphp_empty_object_new(Z_OBJCE_P(getThis()) TSRMLS_CC);
	Z_TYPE_P(self) = IS_OBJECT;
	
	field = ONPHP_READ_PROPERTY(getThis(), "field");
	words = ONPHP_READ_PROPERTY(getThis(), "words");
	logic = ONPHP_READ_PROPERTY(getThis(), "logic");
	
	ONPHP_CALL_METHOD_2(dao, "guessatom", &atom, field, query);
	
	// emulating constructor
	onphp_full_text_sanity_check(atom, words TSRMLS_CC);
	
	if (EG(exception)) {
		ZVAL_FREE(atom);
		return;
	}
	
	ONPHP_FULL_TEXT_MASS_UPDATE(atom, words, logic);
	
	ZVAL_FREE(atom);
	
	RETURN_ZVAL(self, 1, 1);
}

#undef ONPHP_FULL_TEXT_MASS_UPDATE

ONPHP_METHOD(FullText, toBoolean)
{
	ONPHP_THROW(UnsupportedMethodException, NULL);
}

static ONPHP_ARGINFO_THREE;
static ONPHP_ARGINFO_TO_MAPPED;
static ONPHP_ARGINFO_FORM;

zend_function_entry onphp_funcs_FullText[] = {
	ONPHP_ME(FullText, __construct, arginfo_three, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(FullText, toMapped, arginfo_to_mapped, ZEND_ACC_PUBLIC)
	ONPHP_ME(FullText, toBoolean, arginfo_form, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
