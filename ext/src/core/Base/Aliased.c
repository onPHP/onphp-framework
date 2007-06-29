/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp_core.h"

#include "core/Base/Aliased.h"

zend_function_entry onphp_funcs_Aliased[] = {
	ONPHP_ABSTRACT_ME(Aliased, getAlias, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
