/* $Id$ */

#ifndef ONPHP_UTIL_H
#define ONPHP_UTIL_H

#include "php.h"
#include "ext/standard/php_smart_str.h"

extern void onphp_append_zval_to_smart_string(smart_str *string, zval *value);

#endif /* ONPHP_UTIL_H */
