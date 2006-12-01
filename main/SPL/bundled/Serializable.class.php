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
	 * Interface for customized serializing.
	 *
	 * @ingroup onSPL
	**/
	interface Serializable
	{
		/**
		 * @return string representation of the instance
		**/
		public function serialize();
		
		/**
		 * @note This is a constructor
		 * 
		 * @param $serialized data read from stream to construct the instance
		 */
		public function unserialize($serialized);
	}
?>