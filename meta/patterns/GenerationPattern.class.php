<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Patterns
	**/
	interface GenerationPattern
	{
		/// builds everything for given class
		public function build(MetaClass $class);
		
		/// indicates DAO availability for classes which uses this pattern
		public function daoExists();
		
		/// forcing patterns to be singletones
		public static function getInstance();
	}
?>