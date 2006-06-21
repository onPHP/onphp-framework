/* $Id$ */

#include "onphp_core.h"

#include "zend_exceptions.h"

#include "core/Base/Singleton.h"
#include "core/Exceptions.h"

PHPAPI zend_class_entry *onphp_ce_Singleton;

static zval *instances = NULL;

/* protected */		ONPHP_METHOD(Singleton, __construct)	{/*_*/}
/* final private */	ONPHP_METHOD(Singleton, __clone)		{/*_*/}

ONPHP_METHOD(Singleton, getInstance)
{
	char *name;
	int length, argc = ZEND_NUM_ARGS();
	zend_class_entry **cep;
	zval *object, *args;
	zval **stored;
	zval ***params = NULL;

	if (!instances) {
		ALLOC_INIT_ZVAL(instances);
		array_init(instances);
	}
	
	if (argc) {
		params = safe_emalloc(sizeof(zval **), argc, 0);
		
		if (zend_get_parameters_array_ex(argc, params) == FAILURE) {
			zend_throw_exception_ex(
				onphp_ce_BaseException,
				0 TSRMLS_CC,
				"Failed to get calling arguments for object creation"
			);
			efree(params);
			RETURN_NULL();
		}
		
		// replica of historical Singleton's behaviour
		if (argc > 2) {
			int i;
			ALLOC_INIT_ZVAL(args);
			array_init(args);
			
			for (i = 1; i < argc; i++) {
				add_next_index_zval(args, *params[i]);
			}
			
			params[1] = &args;
			argc = 2;
		}
		
		name = estrdup(Z_STRVAL_PP(params[0]));
	} else {
		// ignore params stuff, since it's constructorless
		name = estrdup("SingletonInstance");
	}
		
	length = strlen(name);
	
	if (
		zend_hash_find(
			Z_ARRVAL_P(instances),
			name,
			length + 1,
			(void **) &stored
		) == SUCCESS
	) {
		object = *stored;
		zval_copy_ctor(object);
	} else {
		// stolen from Reflection's newInstance()
		if (zend_lookup_class(name, length, &cep TSRMLS_CC) == SUCCESS) {
			zval *retval_ptr;
			zend_fcall_info fci;
			zend_fcall_info_cache fcc;
			zend_class_entry *ce = *cep;
			
			// can use ce->name instead now
			efree(name);
			
			if (!instanceof_function(ce, onphp_ce_Singleton TSRMLS_CC)) {
				zend_throw_exception_ex(
					onphp_ce_WrongArgumentException,
					0 TSRMLS_CC,
					"Class '%s' is something not a Singleton's child",
					ce->name
				);
				if (argc) {
					efree(params);
				}
				RETURN_NULL();
			}
			
			// we can call protected consturctors,
			// since all classes are childs of Singleton
			if (ce->constructor->common.fn_flags & ZEND_ACC_PRIVATE) {
				zend_throw_exception_ex(
					onphp_ce_BaseException,
					0 TSRMLS_CC,
					"Can not call private constructor for '%s' creation",
					ce->name
				);
				if (argc) {
					efree(params);
				}
				RETURN_NULL();
			} else if (ce->constructor->common.fn_flags & ZEND_ACC_PUBLIC) {
				zend_throw_exception_ex(
					onphp_ce_BaseException,
					0 TSRMLS_CC,
					"Don't want to deal with '%s' class "
						"due to public constructor there",
					ce->name
				);
				if (argc) {
					efree(params);
				}
				RETURN_NULL();
			}

			MAKE_STD_ZVAL(object);
			object_init_ex(object, ce);
			
			fci.size = sizeof(fci);
			fci.function_table = EG(function_table);
			fci.function_name = NULL;
			fci.symbol_table = NULL;
			fci.object_pp = &object;
			fci.retval_ptr_ptr = &retval_ptr;
			if (argc) {
				fci.param_count = argc - 1;
				fci.params = params + 1;
			} else {
				fci.param_count = 0;
			}
			
			fcc.initialized = 1;
			fcc.function_handler = ce->constructor;
			fcc.calling_scope = EG(scope);
			fcc.object_pp = &object;
			
			if (zend_call_function(&fci, &fcc TSRMLS_CC) == FAILURE) {
				zend_throw_exception_ex(
					onphp_ce_BaseException,
					0 TSRMLS_CC,
					"Failed to call '%s' constructor",
					ce->name
				);
			}
			
			if (retval_ptr) {
				zval_ptr_dtor(&retval_ptr);
			}
			
			if (argc) {
				efree(params);
			}

			add_assoc_zval_ex(instances, ce->name, length + 1, object);
		}
	}
	
	RETURN_ZVAL(object, 1, 0);
}

PHP_RSHUTDOWN_FUNCTION(Singleton)
{
	if (instances) {
		zval_ptr_dtor(&instances);
	}

	return SUCCESS;
}

zend_function_entry onphp_funcs_Singleton[] = {
	ONPHP_ME(Singleton, __construct,	NULL, ZEND_ACC_PROTECTED)
	ONPHP_ME(Singleton, getInstance,	NULL, ZEND_ACC_FINAL | ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
	ONPHP_ME(Singleton, __clone,		NULL, ZEND_ACC_FINAL | ZEND_ACC_PRIVATE)
	{NULL, NULL, NULL}
};
