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

#include "onphp.h"

ONPHP_GETTER(RangedPrimitive, getMin, min);
ONPHP_SETTER_LONG(RangedPrimitive, setMin, min);

ONPHP_GETTER(RangedPrimitive, getMax, max);
ONPHP_SETTER_LONG(RangedPrimitive, setMax, max);

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_RangedPrimitive[] = {
	ONPHP_ME(RangedPrimitive, getMin, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(RangedPrimitive, setMin, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(RangedPrimitive, getMax, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(RangedPrimitive, setMax, arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
