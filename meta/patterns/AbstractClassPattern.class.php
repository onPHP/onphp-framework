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
	class AbstractClassPattern extends BasePattern
	{
		public function build(MetaClass $class)
		{
			$class->setType(
				new MetaClassType(
					MetaClassType::CLASS_ABSTRACT
				)
			);
			
			parent::fullBuild($class);
		}
	}
?>