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

	final class DictionaryClassPattern extends AbstractClassPattern
	{
		public function build(MetaClass $class)
		{
			parent::build($class);
			
			$this->dumpFile(
				ONPHP_META_AUTO_DIR.'Auto'.$class->getName().'DAO'.EXT_CLASS,
				DictionaryDaoBuilder::build($class)
			);
			
			$userFile = ONPHP_META_DAO_DIR.$class->getName().'DAO'.EXT_CLASS;
			
//			if (!file_exists($userFile))
				$this->dumpFile(
					$userFile,
					DaoBuilder::build($class)
				);
		}
		
		public function daoExist()
		{
			return true;
		}
	}
?>