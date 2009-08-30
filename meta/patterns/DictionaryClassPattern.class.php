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
	final class DictionaryClassPattern extends BasePattern
	{
		public function build(MetaClass $class)
		{
			parent::fullBuild($class);
			
			// huh?
		}
		
		public function daoExists()
		{
			return true;
		}
	}
?>