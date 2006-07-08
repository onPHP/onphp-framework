/* $Id$ */

#ifndef ONPHP_MAIN_SHARED_MEMORY_SEGMENT_HANDLER_H
#define ONPHP_MAIN_SHARED_MEMORY_SEGMENT_HANDLER_H

#define SMSH_SEGMENT_SIZE 2097152

PHPAPI zend_class_entry *onphp_ce_SharedMemorySegmentHandler;

extern zend_function_entry onphp_funcs_SharedMemorySegmentHandler[];

#endif /* ONPHP_MAIN_SHARED_MEMORY_SEGMENT_HANDLER_H */
