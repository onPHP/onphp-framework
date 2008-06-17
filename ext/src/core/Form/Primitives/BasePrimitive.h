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

#ifndef ONPHP_CORE_BASE_PRIMITIVE_H
#define ONPHP_CORE_BASE_PRIMITIVE_H

ONPHP_STANDART_CLASS(BasePrimitive);

#define ONPHP_ARGINFO_BASE_PRIMITIVE \
	ZEND_BEGIN_ARG_INFO(arginfo_base_primitive, 0) \
		ZEND_ARG_OBJ_INFO(0, prm, BasePrimitive, 0) \
	ZEND_END_ARG_INFO()

#define ONPHP_ARGINFO_ONE_AND_BASE_PRIMITIVE \
	ZEND_BEGIN_ARG_INFO(arginfo_one_and_base_primitive, 0) \
		ZEND_ARG_INFO(0, first) \
		ZEND_ARG_OBJ_INFO(0, prm, BasePrimitive, 0) \
	ZEND_END_ARG_INFO()

#endif /* ONPHP_CORE_BASE_PRIMITIVE_H */
