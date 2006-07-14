/* $Id$ */

#include "onphp.h"
#include "onphp_util.h"

#include "ext/standard/php_string.h"
#include "zend_globals.h"
#include "zend_exceptions.h"
#include "zend_interfaces.h"

#include "core/Base/Singleton.h"
#include "core/DB/Dialect.h"
#include "core/DB/ImaginaryDialect.h"
#include "core/OSQL/DBValue.h"
#include "core/OSQL/DialectString.h"

PHPAPI zend_class_entry *onphp_ce_ImaginaryDialect;

ONPHP_METHOD(ImaginaryDialect, me)
{
	zval *instance, *class;
	
	MAKE_STD_ZVAL(class);
	ZVAL_STRING(class, onphp_ce_ImaginaryDialect->name, 1);
	
	zend_call_method_with_1_params(
		NULL,
		onphp_ce_Singleton,
		NULL,
		"getinstance",
		&instance,
		class
	);
	
	if (EG(exception)) {
		return;
	}

	RETURN_ZVAL(instance, 1, 1);
}

ONPHP_METHOD(ImaginaryDialect, quoteValue)
{
	zval *value;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	RETURN_ZVAL(value, 1, 0);
}

ONPHP_METHOD(ImaginaryDialect, quoteField)
{
	zval *field;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &field) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	RETURN_ZVAL(field, 1, 0);
}

ONPHP_METHOD(ImaginaryDialect, quoteTable)
{
	zval *table;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &table) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	RETURN_ZVAL(table, 1, 0);
}

ONPHP_METHOD(ImaginaryDialect, autoincrementize)
{
	RETURN_STRING("AUTOINCREMENT", 1);
}

ONPHP_METHOD(ImaginaryDialect, fieldToString)
{
	zval *field, *out;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &field) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	if (
		Z_TYPE_P(field) == IS_OBJECT
		&& instanceof_function(Z_OBJCE_P(field), onphp_ce_DialectString TSRMLS_CC)
	) {
		zend_call_method_with_1_params(
			&field,
			Z_OBJCE_P(field),
			NULL,
			"todialectstring",
			&out,
			getThis()
		);
		
		if (EG(exception)) {
			return;
		}
		
		RETURN_ZVAL(out, 1, 1);
	} else {
		RETURN_ZVAL(field, 1, 0);
	}
}

ONPHP_METHOD(ImaginaryDialect, valueToString)
{
	zval *value;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	if (
		Z_TYPE_P(value) == IS_OBJECT
		&& instanceof_function(Z_OBJCE_P(value), onphp_ce_DBValue TSRMLS_CC)
	) {
		zval *out;
		
		SEPARATE_ZVAL_TO_MAKE_IS_REF(&value);
		
		
		zend_call_method_with_1_params(
			&value,
			Z_OBJCE_P(value),
			NULL,
			"todialectstring",
			&out,
			value
		);
		
		if (EG(exception)) {
			return;
		}
		
		RETURN_ZVAL(out, 1, 1);
	}
	
	RETURN_ZVAL(value, 1, 0);
}

ONPHP_METHOD(ImaginaryDialect, fullTextSearch)
{
	smart_str out = {0};
	zval *field, *words, *logic;
	
	if (
		zend_parse_parameters(
			ZEND_NUM_ARGS() TSRMLS_CC,
			"zzz",
			&field,
			&words,
			&logic
		)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	php_implode(logic, words, return_value);

	smart_str_appends(&out, "(\"");
	onphp_append_zval_to_smart_string(&out, field);
	smart_str_appends(&out, "\" CONTAINS \"");
	smart_str_appends(&out, Z_STRVAL_P(return_value));
	smart_str_appends(&out, "\")");
	smart_str_0(&out);
	
	RETURN_STRINGL(out.c, out.len, 0);
}

ONPHP_METHOD(ImaginaryDialect, fullTextRank)
{
	smart_str out = {0};
	zval *field, *words, *logic;
	
	if (
		zend_parse_parameters(
			ZEND_NUM_ARGS() TSRMLS_CC,
			"zzz",
			&field,
			&words,
			&logic
		)
		== FAILURE
	) {
		WRONG_PARAM_COUNT;
	}
	
	php_implode(logic, words, return_value);
	
	smart_str_appends(&out, "(RANK BY \"");
	onphp_append_zval_to_smart_string(&out, field);
	smart_str_appends(&out, "\" WHICH CONTAINS \"");
	smart_str_appends(&out, Z_STRVAL_P(return_value));
	smart_str_appends(&out, "\")");
	smart_str_0(&out);
	
	RETURN_STRINGL(out.c, out.len, 0);
}

static ONPHP_ARGINFO_AUTOINCREMENTIZE;
static ONPHP_ARGINFO_ONE_REF;
static ONPHP_ARGINFO_THREE;

zend_function_entry onphp_funcs_ImaginaryDialect[] = {
	ONPHP_ME(ImaginaryDialect, me,	NULL, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(ImaginaryDialect, autoincrementize, arginfo_autoincrementize, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(ImaginaryDialect, quoteValue, arginfo_one_ref, ZEND_ACC_PUBLIC |  ZEND_ACC_STATIC)
	ONPHP_ME(ImaginaryDialect, quoteField, arginfo_one_ref, ZEND_ACC_PUBLIC |  ZEND_ACC_STATIC)
	ONPHP_ME(ImaginaryDialect, quoteTable, arginfo_one_ref, ZEND_ACC_PUBLIC |  ZEND_ACC_STATIC)
	ONPHP_ME(ImaginaryDialect, fieldToString, arginfo_one_ref, ZEND_ACC_PUBLIC)
	ONPHP_ME(ImaginaryDialect, valueToString, arginfo_one_ref, ZEND_ACC_PUBLIC)
	ONPHP_ME(ImaginaryDialect, fullTextSearch, arginfo_three, ZEND_ACC_PUBLIC)
	ONPHP_ME(ImaginaryDialect, fullTextRank, arginfo_three, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
