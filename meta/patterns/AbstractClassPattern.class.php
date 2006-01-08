<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class AbstractClassPattern extends BasePattern
	{
		public function build(MetaClass $class)
		{
			$this->dumpFile(
				ONPHP_META_AUTO_DIR.'Auto'.$class->getName().EXT_CLASS,
				AutoClassBuilder::build($class)
			);
			
			$userFile = ONPHP_META_BUSINESS_DIR.$class->getName().EXT_CLASS;
			
			if (!file_exists($userFile))
				$this->dumpFile(
					$userFile,
					BusinessClassBuilder::build($class)
				);
		}
	}
?>