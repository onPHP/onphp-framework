/* $Id$ */

#ifndef ONPHP_H
#define ONPHP_H

#define ONPHP_VERSION "$Id$"

#define ONPHP_ME(class_name, function_name, arg_info, flags) \
	PHP_ME(onphp_ ## class_name, function_name, arg_info, flags)

#define ONPHP_ABSTRACT_ME(class_name, function_name, arg_info, flags) \
	ZEND_FENTRY(function_name, NULL, arg_info, flags|ZEND_ACC_ABSTRACT)

#define REGISTER_ONPHP_INTERFACE(class_name) \
	spl_register_interface(&onphp_ce_ ## class_name, # class_name, onphp_funcs_ ## class_name TSRMLS_CC);

#define REGISTER_ONPHP_IMPLEMENTS(class_name, interface_name) \
	zend_class_implements(onphp_ce_ ## class_name TSRMLS_CC, 1, onphp_ce_ ## interface_name);

#define REGISTER_ONPHP_STD_CLASS(class_name, obj_ctor) \
	spl_register_std_class(&onphp_ce_ ## class_name, # class_name, obj_ctor, NULL TSRMLS_CC);

#define REGISTER_ONPHP_STD_CLASS_EX(class_name, obj_ctor, funcs) \
	spl_register_std_class(&onphp_ce_ ## class_name, # class_name, obj_ctor, funcs TSRMLS_CC);

#define REGISTER_ONPHP_SUB_CLASS_EX(class_name, parent_class_name, obj_ctor, funcs) \
	spl_register_sub_class(&onphp_ce_ ## class_name, onphp_ce_ ## parent_class_name, # class_name, obj_ctor, funcs TSRMLS_CC);

#define REGISTER_ONPHP_PROPERTY(class_name, prop_name, prop_flags) \
	zend_declare_property_null(onphp_ce_ ## class_name, prop_name, strlen(prop_name), prop_flags TSRMLS_CC);

#define ONPHP_READ_PROPERTY(class, property) \
	zend_read_property(Z_OBJCE_P(class), class, property, strlen(property), 1 TSRMLS_CC)

#define ONPHP_UPDATE_PROPERTY(class, property, value) \
	zend_update_property(Z_OBJCE_P(class), class, property, strlen(property), value TSRMLS_CC)

#define ONPHP_METHOD(class_name, function_name) \
	PHP_METHOD(onphp_ ## class_name, function_name)

#endif /* ONPHP_H */
