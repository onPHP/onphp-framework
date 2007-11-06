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

ONPHP_SETTER(SgmlToken, setValue, value);
ONPHP_GETTER(SgmlToken, getValue, value);

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_SgmlToken[] = {
	ONPHP_ME(SgmlToken, setValue, arginfo_one, ZEND_ACC_PUBLIC)
	ONPHP_ME(SgmlToken, getValue, NULL, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
