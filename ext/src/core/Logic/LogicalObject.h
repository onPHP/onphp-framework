/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#ifndef ONPHP_CORE_LOGICAL_OBJECT_H
#define ONPHP_CORE_LOGICAL_OBJECT_H

ONPHP_STANDART_CLASS(LogicalObject);

#define ONPHP_ARGINFO_LOGICAL_OBJECT \
	ZEND_BEGIN_ARG_INFO(arginfo_logical_object, 0) \
		ZEND_ARG_OBJ_INFO(0, exp, LogicalObject, 0) \
	ZEND_END_ARG_INFO()

#define ONPHP_ARGINFO_LOGICAL_OBJECT_AND_ONE \
	ZEND_BEGIN_ARG_INFO(arginfo_logical_object_and_one, 0) \
		ZEND_ARG_OBJ_INFO(0, exp, LogicalObject, 0) \
		ZEND_ARG_INFO(0, logic) \
	ZEND_END_ARG_INFO()

#define ONPHP_ARGINFO_ONE_AND_LOGICAL_OBJECT \
	ZEND_BEGIN_ARG_INFO(arginfo_one_and_logical_object, 0) \
		ZEND_ARG_INFO(0, name) \
		ZEND_ARG_OBJ_INFO(0, rule, LogicalObject, 0) \
	ZEND_END_ARG_INFO()

#endif /* ONPHP_CORE_LOGICAL_OBJECT_H */
