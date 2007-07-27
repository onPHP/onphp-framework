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

#ifndef ONPHP_UTIL_H
#define ONPHP_UTIL_H

#include "php.h"
#include "ext/standard/php_smart_str.h"

extern void onphp_append_zval_to_smart_string(smart_str *string, zval *value);

#endif /* ONPHP_UTIL_H */
