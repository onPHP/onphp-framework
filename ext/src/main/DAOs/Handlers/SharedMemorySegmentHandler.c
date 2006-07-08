/* $Id$ */

#include "onphp.h"

#include "main/DAOs/Handlers/SharedMemorySegmentHandler.h"

PHPAPI zend_class_entry *onphp_ce_SharedMemorySegmentHandler;

static ONPHP_ARGINFO_ONE;

static int le_onphp_smsh;

static void _onphp_smsh_pool_list_dtor(zend_rsrc_list_entry *rsrc TSRMLS_DC)
{
	zend_hash_destroy((HashTable *) rsrc->ptr);
}

PHP_MINIT_FUNCTION(SharedMemorySegmentHandler)
{
	le_onphp_smsh =
		zend_register_list_destructors_ex(
			NULL,
			_onphp_smsh_pool_list_dtor,
			"SharedMemorySegmentHandler's pool",
			module_number
		);
	
	return SUCCESS;
}

static HashTable *onphp_smsh_find_segment(long id TSRMLS_DC)
{
	char *hash_key;
	int hash_key_len;
	list_entry *le;
	HashTable *retval;
	
	hash_key = emalloc(sizeof("onphp_smsh___") + MAX_LENGTH_OF_LONG);
	hash_key_len = sprintf(hash_key, "onphp_smsh___%ld", id);
	
	if (
		zend_hash_find(
			&EG(persistent_list),
			hash_key,
			hash_key_len + 1,
			(void **) &le
		)
		== FAILURE
	) {
		list_entry new_le;
		HashTable ht;
		
		zend_hash_init(&ht, 0, NULL, NULL, 0);
		
		new_le.type = le_onphp_smsh;
		new_le.ptr	= &ht;
		retval = &ht;
		
		if (
			zend_hash_update(
				&EG(persistent_list),
				hash_key,
				hash_key_len + 1,
				(void *) &new_le,
				sizeof(HashTable),
				NULL
			)
			== FAILURE
		) {
			zend_hash_destroy(&ht);
		} else {
			zend_list_insert(&ht, le_onphp_smsh);
		}
	} else {
		retval = le->ptr;
	}
	
	efree(hash_key);
	return retval;
}

ONPHP_METHOD(SharedMemorySegmentHandler, __construct)
{
	long id;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &id) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	ONPHP_UPDATE_PROPERTY_LONG(getThis(), "id", id);
}

ONPHP_METHOD(SharedMemorySegmentHandler, touch)
{
	long id;
	zval *key;
	HashTable *segment;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &key) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	convert_to_string(key);	
	
	id = Z_LVAL_P(ONPHP_READ_PROPERTY(getThis(), "id"));
	
	segment = onphp_smsh_find_segment(id TSRMLS_CC);
	
	if (
		zend_hash_update(
			&EG(persistent_list),
			Z_STRVAL_P(key),
			Z_STRLEN_P(key) + 1,
			(void *) &id,
			sizeof(long),
			NULL
		)
		== FAILURE
	) {
		RETURN_FALSE;
	}
	
	RETURN_TRUE;
}

ONPHP_METHOD(SharedMemorySegmentHandler, unlink)
{
	long id;
	zval *key;
	HashTable *segment;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &key) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	convert_to_string(key);	
	
	id = Z_LVAL_P(ONPHP_READ_PROPERTY(getThis(), "id"));
	
	segment = onphp_smsh_find_segment(id TSRMLS_CC);
	
	if (
		zend_hash_del(
			&EG(persistent_list),
			Z_STRVAL_P(key),
			Z_STRLEN_P(key) + 1
		)
		== FAILURE
	) {
		RETURN_FALSE;
	}
	
	RETURN_TRUE;
}

ONPHP_METHOD(SharedMemorySegmentHandler, ping)
{
	long id;
	zval *key;
	HashTable *segment;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &key) == FAILURE) {
		WRONG_PARAM_COUNT;
	}
	
	convert_to_string(key);	
	
	id = Z_LVAL_P(ONPHP_READ_PROPERTY(getThis(), "id"));
	
	segment = onphp_smsh_find_segment(id TSRMLS_CC);
	
	if (
		zend_hash_find(
			&EG(persistent_list),
			Z_STRVAL_P(key),
			Z_STRLEN_P(key) + 1,
			(void *) &id
		)
		== FAILURE
	) {
		RETURN_FALSE;
	}
	
	RETURN_TRUE;
}

ONPHP_METHOD(SharedMemorySegmentHandler, drop)
{
	zend_hash_destroy(
		onphp_smsh_find_segment(
			Z_LVAL_P(
				ONPHP_READ_PROPERTY(getThis(), "id")
			)
			TSRMLS_CC
		)
	);
	
	RETURN_TRUE;
}


zend_function_entry onphp_funcs_SharedMemorySegmentHandler[] = {
	ONPHP_ME(SharedMemorySegmentHandler, __construct,	arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(SharedMemorySegmentHandler, touch,			arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(SharedMemorySegmentHandler, unlink,		arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(SharedMemorySegmentHandler, ping,			arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(SharedMemorySegmentHandler, drop,			NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
