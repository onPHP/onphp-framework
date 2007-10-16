<?php
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

	/**
	 * @ingroup DAOs
	 * @ingroup Module
	**/
	interface SegmentHandler
	{
		public function __construct($segmentId);
		
		/// checks for a key existence in segment
		public function ping($key);
		
		/// creates key in segment
		public function touch($key);
		
		/// deletes key from segment
		public function unlink($key);
		
		/// destroys segment
		public function drop();
	}
?>