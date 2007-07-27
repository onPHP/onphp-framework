/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#ifndef ONPHP_CORE_EXCEPTIONS_H
#define ONPHP_CORE_EXCEPTIONS_H

#include "php.h"
#include "ext/spl/spl_functions.h"

PHPAPI zend_class_entry *onphp_ce_BaseException;
PHPAPI zend_class_entry *onphp_ce_BusinessLogicException;
PHPAPI zend_class_entry *onphp_ce_ClassNotFoundException;
PHPAPI zend_class_entry *onphp_ce_DatabaseException;
PHPAPI zend_class_entry *onphp_ce_DuplicateObjectException;
PHPAPI zend_class_entry *onphp_ce_FileNotFoundException;
PHPAPI zend_class_entry *onphp_ce_IOException;
PHPAPI zend_class_entry *onphp_ce_IOTimedOutException;
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
