<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Concrete information about DB stuff.
	 * 
	 * @ingroup DAOs
	**/
	interface InformedDAO
	{
		public static function getTable();
		public static function getSequence();
		public static function getObjectName();
		public static function getIdName();
	}
?>