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

#include "onphp.h"

#include "zend_exceptions.h"

#include "core/Exceptions.h"

#if (PHP_MAJOR_VERSION == 5) && (PHP_MINOR_VERSION < 2)
#define onphp_ce_Exception zend_exception_get_default()
#else
#define onphp_ce_Exception zend_exception_get_default(TSRMLS_C)
#endif

PHP_MINIT_FUNCTION(Exceptions)
{
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(BusinessLogicException,			Exception,			NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(UnimplementedFeatureException,	Exception,			NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(SecurityException,				Exception,			NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(BaseException,					Exception,			NULL, NULL);
	
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(ClassNotFoundException,			BaseException,		NULL, NULL);
	ONPHP_CLASS_IS_FINAL(ClassNotFoundException);
	
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(FileNotFoundException,			BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(IOException,						BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(MissingElementException,			BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(NetworkException,				BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(UnsupportedMethodException,		BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(WrongArgumentException,			BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(WrongStateException,				BaseException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(DatabaseException,				BaseException,		NULL, NULL);
	
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(DuplicateObjectException,		DatabaseException,	NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(ObjectNotFoundException,			DatabaseException,	NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(TooManyRowsException,			DatabaseException,	NULL, NULL);
	
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(NetworkException,				IOException,		NULL, NULL);
	REGISTER_ONPHP_CUSTOM_SUB_CLASS_EX(IOTimedOutException,				IOException,		NULL, NULL);
	
	return SUCCESS;
}
