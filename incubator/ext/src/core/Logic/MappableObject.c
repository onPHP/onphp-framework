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

#include "core/Logic/MappableObject.h"

static ONPHP_ARGINFO_TO_MAPPED;

zend_function_entry onphp_funcs_MappableObject[] = {
	ONPHP_ABSTRACT_ME(MappableObject, toMapped, arginfo_to_mapped, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
