/* $Id$ */

#include "onphp_main.h"
#include "onphp_util.h"

#include "main/DAOs/Handlers/SegmentHandler.h"
#include "main/DAOs/Handlers/SharedMemorySegmentHandler.h"

PHP_MINIT_FUNCTION(onphp_main)
{
	REGISTER_ONPHP_INTERFACE(SegmentHandler);
/*
	broken by design
	
	REGISTER_ONPHP_STD_CLASS_EX(SharedMemorySegmentHandler);
	REGISTER_ONPHP_PROPERTY(SharedMemorySegmentHandler, "id", ZEND_ACC_PRIVATE);
	REGISTER_ONPHP_IMPLEMENTS(SharedMemorySegmentHandler, SegmentHandler);
	REGISTER_ONPHP_CLASS_CONST_LONG(SharedMemorySegmentHandler, "SEGMENT_SIZE", SMSH_SEGMENT_SIZE);
	onphp_ce_SharedMemorySegmentHandler->ce_flags |= ZEND_ACC_FINAL_CLASS;
	
	return PHP_MINIT(SharedMemorySegmentHandler)(INIT_FUNC_ARGS_PASSTHRU);
*/
	
	return SUCCESS;
}
