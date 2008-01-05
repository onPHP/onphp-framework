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

#include "ext/spl/spl_functions.h"

#include "onphp_main.h"
#include "onphp_util.h"

#include "core/Base/Identifiable.h"

#include "main/DAOs/DAOConnected.h"
#include "main/DAOs/FullTextDAO.h"
#include "main/DAOs/Handlers/SegmentHandler.h"

#include "main/Flow/ViewResolver.h"

#include "main/Markup/Html/SgmlEndTag.h"
#include "main/Markup/Html/SgmlTag.h"
#include "main/Markup/Html/SgmlToken.h"
#include "main/Markup/Html/Cdata.h"

PHP_MINIT_FUNCTION(onphp_main)
{
	REGISTER_ONPHP_INTERFACE(SegmentHandler);
	REGISTER_ONPHP_INTERFACE(ViewResolver);
	REGISTER_ONPHP_INTERFACE(FullTextDAO);
	
	REGISTER_ONPHP_INTERFACE(DAOConnected);
	REGISTER_ONPHP_IMPLEMENTS(DAOConnected, Identifiable);
	
	REGISTER_ONPHP_STD_CLASS(SgmlToken);
	REGISTER_ONPHP_PROPERTY(SgmlToken, "value", ZEND_ACC_PRIVATE);
	
	REGISTER_ONPHP_SUB_CLASS(Cdata, SgmlToken);
	REGISTER_ONPHP_PROPERTY(Cdata, "data", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_PROPERTY_BOOL(Cdata, "strict", 0, ZEND_ACC_PRIVATE);
	ONPHP_CLASS_IS_FINAL(Cdata);
	
	REGISTER_ONPHP_SUB_CLASS(SgmlTag, SgmlToken);
	REGISTER_ONPHP_PROPERTY(SgmlTag, "id", ZEND_ACC_PRIVATE);
	ONPHP_CLASS_IS_ABSTRACT(SgmlTag);
	
	REGISTER_ONPHP_SUB_CLASS(SgmlEndTag, SgmlTag);
	ONPHP_CLASS_IS_FINAL(SgmlEndTag);
	
	return SUCCESS;
}
