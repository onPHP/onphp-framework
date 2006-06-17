/* $Id$ */

#ifndef ONPHP_CORE_EXCEPTIONS_H
#define ONPHP_CORE_EXCEPTIONS_H

#include "php.h"

extern PHPAPI zend_class_entry *onphp_ce_BaseException;
extern PHPAPI zend_class_entry *onphp_ce_BusinessLogicException;
extern PHPAPI zend_class_entry *onphp_ce_DatabaseException;
extern PHPAPI zend_class_entry *onphp_ce_DuplicateObjectException;
extern PHPAPI zend_class_entry *onphp_ce_MissingElementException;
extern PHPAPI zend_class_entry *onphp_ce_NetworkException;
extern PHPAPI zend_class_entry *onphp_ce_ObjectNotFoundException;
extern PHPAPI zend_class_entry *onphp_ce_TooManyRowsException;
extern PHPAPI zend_class_entry *onphp_ce_UnimplementedFeatureException;
extern PHPAPI zend_class_entry *onphp_ce_UnsupportedMethodException;
extern PHPAPI zend_class_entry *onphp_ce_WrongArgumentException;
extern PHPAPI zend_class_entry *onphp_ce_WrongStateException;

PHP_MINIT_FUNCTION(Exceptions);

#endif /* ONPHP_CORE_EXCEPTIONS_H */
