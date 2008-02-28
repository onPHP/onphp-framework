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
	zval copy;

	if (Z_TYPE_P(value) == IS_STRING) {
		smart_str_appends(string, Z_STRVAL_P(value));
	} else {
		int use_copy;
		
		zend_make_printable_zval(value, &copy, &use_copy);
		smart_str_appends(string, Z_STRVAL(copy));
		
		if (use_copy) {
			zval_dtor(&copy);
		}
	}
}
