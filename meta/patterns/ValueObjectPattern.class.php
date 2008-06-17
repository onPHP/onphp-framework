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
	 * @ingroup Patterns
	**/
	final class ValueObjectPattern extends BasePattern
	{
		public function tableExists()
		{
			return false;
		}
		
		public function daoExists()
		{
			return true;
		}
		
		/**
		 * @return ValueObjectPattern
		**/
		protected function buildDao(MetaClass $class)
		{
			$this->dumpFile(
				ONPHP_META_AUTO_DAO_DIR.'Auto'.$class->getName().'DAO'.EXT_CLASS,
				Format::indentize(ValueObjectDaoBuilder::build($class))
			);
			
			$userFile = ONPHP_META_DAO_DIR.$class->getName().'DAO'.EXT_CLASS;
			
			if (
				MetaConfiguration::me()->isForcedGeneration()
				|| !file_exists($userFile)
			)
				$this->dumpFile(
					$userFile,
					Format::indentize(DaoBuilder::build($class))
				);
			
			return $this;
		}
	}
?>