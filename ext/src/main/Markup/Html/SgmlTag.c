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

ONPHP_GETTER(SgmlTag, getId, id);
ONPHP_SETTER(SgmlTag, setId, id);

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_SgmlTag[] = {
	ONPHP_ME(SgmlTag, getId, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(SgmlTag, setId, arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
