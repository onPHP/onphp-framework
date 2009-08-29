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

#ifndef ONPHP_CORE_FILTRATOR_H
#define ONPHP_CORE_FILTRATOR_H

ONPHP_STANDART_CLASS(Filtrator);

#define ONPHP_ARGINFO_FILTRATOR \
	ZEND_BEGIN_ARG_INFO(arginfo_filtrator, 0) \
		ZEND_ARG_OBJ_INFO(0, filter, Filtrator, 0) \
	ZEND_END_ARG_INFO()

#endif /* ONPHP_CORE_FILTRATOR_H */
