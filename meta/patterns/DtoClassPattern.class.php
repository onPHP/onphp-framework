<?php
/***************************************************************************
 *   Copyright (C) 2007 by Denis Gabaidulin                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Patterns
	**/
	final class DTOClassPattern extends BasePattern
	{
		public function tableExists()
		{
			return false;
		}
		
		public function daoExists()
		{
			return false;
		}
		
		/**
		 * @return DTOPattern
		**/
		protected function buildProto(MetaClass $class)
		{
			return $this;
		}
		
		protected function buildBusiness(MetaClass $class)
		{
			return $this;
		}
		
		protected function buildDao(MetaClass $class)
		{
			return $this;
		}
		
		protected function fullBuild(MetaClass $class)
		{
			return $this->buildDto($class);
		}
		
		protected function buildDto(MetaClass $class)
		{
			$this->dumpFile(
				ONPHP_META_AUTO_DTO_DIR.'AutoDto'.$class->getName().EXT_CLASS,
				Format::indentize(DtoClassBuilder::build($class))
			);
			
			return $this;
		}
	}
?>