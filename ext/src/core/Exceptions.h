/* $Id$ */

#ifndef ONPHP_CORE_EXCEPTIONS_H
#define ONPHP_CORE_EXCEPTIONS_H

#include "php.h"

PHPAPI zend_class_entry *onphp_ce_BaseException;
PHPAPI zend_class_entry *onphp_ce_BusinessLogicException;
PHPAPI zend_class_entry *onphp_ce_DatabaseException;
PHPAPI zend_class_entry *onphp_ce_DuplicateObjectException;
PHPAPI zend_class_entry *onphp_ce_MissingElementException;
PHPAPI zend_class_entry *onphp_ce_NetworkException;
PHPAPI zend_class_entry *onphp_ce_ObjectNotFoundException;
PHPAPI zend_class_entry *onphp_ce_TooManyRowsException;
PHPAPI zend_class_entry *onphp_ce_UnimplementedFeatureException;
PHPAPI zend_class_entry *onphp_ce_UnsupportedMethodException;
PHPAPI zend_class_entry *onphp_ce_WrongArgumentException;
PHPAPI zend_class_entry *onphp_ce_WrongStateException;

PHP_MINIT_FUNCTION(Exceptions);

#endif /* ONPHP_CORE_EXCEPTIONS_H */
