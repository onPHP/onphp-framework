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

#include "onphp_util.h"

void onphp_append_zval_to_smart_string(smart_str *string, zval *value)
{
	if (Z_TYPE_P(value) == IS_STRING) {
		smart_str_appendl(string, Z_STRVAL_P(value), Z_STRLEN_P(value));
	} else {
		zval copy;
		int use_copy;
		
		zend_make_printable_zval(value, &copy, &use_copy);
		smart_str_appendl(string, Z_STRVAL(copy), Z_STRLEN(copy));
		
		zval_dtor(&copy);
		zval_ptr_dtor(&value);
	}
}
