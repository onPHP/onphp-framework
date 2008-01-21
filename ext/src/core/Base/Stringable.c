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

#include "onphp_core.h"

#include "core/Base/Stringable.h"

zend_function_entry onphp_funcs_Stringable[] = {
	ONPHP_ABSTRACT_ME(Stringable, toString, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
