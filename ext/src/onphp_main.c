/* $Id$ */

#include "onphp_main.h"
#include "onphp_util.h"

#include "main/DAOs/Handlers/SegmentHandler.h"

PHP_MINIT_FUNCTION(onphp_main)
{
	REGISTER_ONPHP_INTERFACE(SegmentHandler);
	
	return SUCCESS;
}
