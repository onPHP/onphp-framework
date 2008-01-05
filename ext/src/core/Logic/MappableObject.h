/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#ifndef ONPHP_CORE_MAPPABLE_OBJECT_H
#define ONPHP_CORE_MAPPABLE_OBJECT_H

ONPHP_STANDART_CLASS(MappableObject);

#define ONPHP_ARGINFO_TO_MAPPED \
	ZEND_BEGIN_ARG_INFO(arginfo_to_mapped, 0) \
		ZEND_ARG_OBJ_INFO(0, dao, ProtoDAO, 0) \
		ZEND_ARG_OBJ_INFO(0, query, JoinCapableQuery, 0) \
	ZEND_END_ARG_INFO()

#endif /* ONPHP_CORE_MAPPABLE_OBJECT_H */
