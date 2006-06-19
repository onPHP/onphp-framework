/* $Id$ */

#include "php.h"
#include "ext/standard/php_string.h"

#include "onphp.h"
#include "onphp_core.h"
#include "onphp_util.h"

#include "core/Exceptions.h"

PHPAPI zend_class_entry *onphp_ce_Dialect;

static
ZEND_BEGIN_ARG_INFO(arginfo_autoincrementize, 0)
	ZEND_ARG_OBJ_INFO(0, DBColumn, DBColumn, 0)
	ZEND_ARG_INFO(1, prepend)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO(arginfo_one_ref, 0)
	ZEND_ARG_INFO(1, value)
ZEND_END_ARG_INFO();

static inline int onphp_surround_string(char *string, char letter)
{
	int length = strlen(string) + 2;
	
	string = erealloc(string, length);
	
	string[length] = 0;
	
	memmove(string + 1, string, length - 2);
	
	string[0] = letter;
	string[length - 1] = letter;
	
	return length;
}

ONPHP_METHOD(Dialect, quoteValue)
{
	zval *value;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		return;
	}

	// don't know, how to replicate original voodoo
	if (Z_TYPE_P(value) == IS_LONG) {
		RETURN_LONG(Z_LVAL_P(value));
	} else {
		char *slashed;
		int length = 0;
		
		slashed =
			php_addslashes(
				Z_STRVAL_P(value),
				Z_STRLEN_P(value),
				&length,
				0 TSRMLS_CC
			);
		
		length = onphp_surround_string(slashed, 39); // 39 == '
		
		RETURN_STRINGL(slashed, length, 0);
	}
}

// FIXME: drop this one, probably. useless emulation
ONPHP_METHOD(Dialect, quoteField)
{
	zval *value, *retval;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		return;
	}

	zend_fcall_info fci;
	zval *destination;
	zval ***params = (zval ***) safe_emalloc(sizeof(zval **), 1, 0);
	params[0] = &value;
	
	ALLOC_INIT_ZVAL(destination);
	array_init(destination);
	
	add_next_index_string(destination, "Dialect", 1); // "self", in fact
	add_next_index_string(destination, "quoteTable", 1);
	
	fci.size = sizeof(fci);
	fci.function_table = &onphp_ce_Dialect->function_table;
	fci.function_name = destination;
	fci.symbol_table = NULL;
	fci.object_pp = NULL;
	fci.retval_ptr_ptr = &retval;
	fci.param_count = 1;
	fci.params = params;
	
	zval_ptr_dtor(&destination);
	
	if (zend_call_function(&fci, NULL TSRMLS_CC) == SUCCESS) {
		RETURN_ZVAL(retval, 1, 0);
	} else {
		zend_throw_exception_ex(
			onphp_ce_BaseException,
			0 TSRMLS_CC,
			"Failed to call self::quoteTable($field)"
		);
	}
}

ONPHP_METHOD(Dialect, quoteTable)
{
	zval *value;
	int length;
	char *quoted;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &value) == FAILURE) {
		return;
	}
	
	quoted = estrndup(Z_STRVAL_P(value), Z_STRLEN_P(value));
	length = onphp_surround_string(quoted, 34); // 34 == "
	
	RETURN_STRINGL(quoted, length, 0);
}

ONPHP_METHOD(Dialect, toCasted)
{
	// return "CAST ({$field} AS {$type})";	
	zval *field, *type;
	smart_str string = {0};
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zz", &field, &type) == FAILURE) {
		return;
	}
	
	smart_str_appends(&string, "CAST (");
	onphp_append_zval_to_smart_string(&string, field);
	smart_str_appends(&string, " AS ");
	onphp_append_zval_to_smart_string(&string, type);
	smart_str_appends(&string, ")");
	smart_str_0(&string);

	RETURN_STRINGL(string.c, string.len, 0);
}

ONPHP_METHOD(Dialect, timeZone)
{
	zend_bool exist = 0;

	zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "b", &exist);
	
	if (exist) {
		RETURN_STRING(" WITH TIME ZONE", 0);
	} else {
		RETURN_STRING(" WITHOUT TIME ZONE", 0);
	}
}

ONPHP_METHOD(Dialect, dropTableMode)
{
	zend_bool cascade = 0;

	zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "b", &cascade);
	
	if (cascade) {
		RETURN_STRING(" CASCADE", 0);
	} else {
		RETURN_STRING(" RESTRICT", 0);
	}
}

ONPHP_METHOD(Dialect, fullTextSearch)
{
	zend_throw_exception_ex(
		onphp_ce_UnimplementedFeatureException,
		0 TSRMLS_CC
	);
}

ONPHP_METHOD(Dialect, fullTextRank)
{
	zend_throw_exception_ex(
		onphp_ce_UnimplementedFeatureException,
		0 TSRMLS_CC
	);
}

zend_function_entry onphp_funcs_Dialect[] = {
	ONPHP_ABSTRACT_ME(Dialect, autoincrementize, arginfo_autoincrementize, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, quoteValue, arginfo_one_ref, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, quoteField, arginfo_one_ref, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, quoteTable, arginfo_one_ref, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, toCasted, arginfo_two, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, timeZone, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, dropTableMode, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Dialect, fullTextSearch, arginfo_three, ZEND_ACC_PUBLIC)
	ONPHP_ME(Dialect, fullTextRank, arginfo_three, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
