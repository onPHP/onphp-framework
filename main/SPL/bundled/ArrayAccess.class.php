<?php
/***************************************************************************
 *   Copyright (C) 2003-2006 by Marcus Boerger                             *
 *                                                                         *
 *   This source file is subject to version 3.01 of the PHP license,       *
 *   that is available through the world-wide-web at the following url:    *
 *   http://www.php.net/license/3_01.txt                                   *
 *                                                                         *
 *   If you did not receive a copy of the PHP license and are unable to    *
 *   obtain it through the world-wide-web, please send a note to           *
 *   license@php.net so we can mail you a copy immediately.                *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Interface to override array access of objects.
	 *
	 * @ingroup onSPL
	**/
	interface ArrayAccess
	{
		/**
		 * @param $offset to modify
		 * @param $value new value
		**/
		public function offsetSet($offset, $value);
		
		/**
		 * @param $offset to retrieve
		 * @return value at given offset
		**/
		public function offsetGet($offset);
		
		/// @param $offset to delete
		public function offsetUnset($offset);
		
		/**
		 * @param $offset to check
		 * @return whether the offset exists.
		**/
		public function offsetExists($offset);
	}
?>