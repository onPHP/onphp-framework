/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp.h"

#include "zend_exceptions.h"

#include "core/Exceptions.h"

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

#if (PHP_MAJOR_VERSION == 5) && (PHP_MINOR_VERSION < 2)
#define onphp_ce_Exception zend_exception_get_default()
#else
#define onphp_ce_Exception zend_exception_get_default(TSRMLS_C)
#endif

PHP_MINIT_FUNCTION(Exceptions)
{
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(BusinessLogicException,			Exception,			NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(UnimplementedFeatureException,	Exception,			NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(BaseException,					Exception,			NULL, NULL);

	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(ClassNotFoundException,			BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(MissingElementException,			BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(NetworkException,				BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(UnsupportedMethodException,		BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(WrongArgumentException,			BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(WrongStateException,				BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(DatabaseException,				BaseException,		NULL, NULL);
	
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(DuplicateObjectException,		DatabaseException,	NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(ObjectNotFoundException,			DatabaseException,	NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(TooManyRowsException,			DatabaseException,	NULL, NULL);

	return SUCCESS;
}
