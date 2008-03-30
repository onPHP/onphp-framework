/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp.h"

#include "core/Form/Form.h"

static ONPHP_ARGINFO_FORM;

zend_function_entry onphp_funcs_LogicalObject[] = {
	ONPHP_ABSTRACT_ME(LogicalObject, toBoolean, arginfo_form, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
