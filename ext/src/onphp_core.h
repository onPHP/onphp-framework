/* $Id$ */

#ifndef ONPHP_CORE_H
#define ONPHP_CORE_H

#include "php.h"

#include "onphp.h"

extern PHP_RINIT_FUNCTION(onphp_core);
extern PHP_MINIT_FUNCTION(onphp_core);
extern PHP_RSHUTDOWN_FUNCTION(onphp_core);

#endif /* ONPHP_CORE_H */
