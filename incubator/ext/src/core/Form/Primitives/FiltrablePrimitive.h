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

#ifndef ONPHP_CORE_FILTRABLE_PRIMITIVE_H
#define ONPHP_CORE_FILTRABLE_PRIMITIVE_H

ONPHP_STANDART_CLASS(FiltrablePrimitive);

#define ONPHP_ARGINFO_FILTER_CHAIN \
	ZEND_BEGIN_ARG_INFO(arginfo_filter_chain, 0) \
		ZEND_ARG_OBJ_INFO(0, chain, FilterChain, 0) \
	ZEND_END_ARG_INFO()

#endif /* ONPHP_CORE_FILTRABLE_PRIMITIVE_H */
