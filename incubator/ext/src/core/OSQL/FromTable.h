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

#ifndef ONPHP_CORE_FROM_TABLE_H
#define ONPHP_CORE_FROM_TABLE_H

ONPHP_STANDART_CLASS(FromTable);

#define ONPHP_ARGINFO_FROM_TABLE \
	ZEND_BEGIN_ARG_INFO(arginfo_from_table, 0) \
		ZEND_ARG_OBJ_INFO(0, from_table, FromTable, 0) \
	ZEND_END_ARG_INFO()

#endif /* ONPHP_CORE_FROM_TABLE_H */
