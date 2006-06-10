/* $Id$ */

#ifndef ONPHP_H
#define ONPHP_H

#define ONPHP_ME(class_name, function_name, arg_info, flags) \
	PHP_ME(onphp_ ## class_name, function_name, arg_info, flags)

#define ONPHP_ABSTRACT_ME(class_name, function_name, arg_info) \
	ZEND_ABSTRACT_ME(onphp_ ## class_name, function_name, arg_info)

#define REGISTER_ONPHP_INTERFACE(class_name) \
	spl_register_interface(&onphp_ce_ ## class_name, # class_name, onphp_funcs_ ## class_name TSRMLS_CC);

#define REGISTER_ONPHP_IMPLEMENTS(class_name, interface_name) \
	zend_class_implements(onphp_ce_ ## class_name TSRMLS_CC, 1, onphp_ce_ ## interface_name);

#define REGISTER_ONPHP_STD_CLASS(class_name, obj_ctor) \
	spl_register_std_class(&onphp_ce_ ## class_name, # class_name, obj_ctor, NULL TSRMLS_CC);

#define REGISTER_ONPHP_STD_CLASS_EX(class_name, obj_ctor, funcs) \
	spl_register_std_class(&onphp_ce_ ## class_name, # class_name, obj_ctor, funcs TSRMLS_CC);

#define REGISTER_ONPHP_PROPERTY(class_name, prop_name, prop_val, prop_flags) \
	zend_declare_property_null(onphp_ce_ ## class_name, prop_name, sizeof(prop_name) - 1, prop_flags TSRMLS_CC);

#define ONPHP_METHOD(class_name, function_name) \
	PHP_METHOD(onphp_ ## class_name, function_name)

#endif /* ONPHP_H */
