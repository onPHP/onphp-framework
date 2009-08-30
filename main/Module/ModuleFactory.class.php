<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Module
	**/
	class ModuleFactory extends StaticFactory
	{
		private static $templateDirectory = null;
		private static $moduleDirectory = null;
		
		public static function spawn($name)
		{
			self::inject($name);
			
			return new $name();
		}
		
		public static function inject($name)
		{
			if (
				!is_dir(self::$moduleDirectory)
				|| !is_dir(self::$templateDirectory)
			)
				throw new WrongArgumentException(
					'specify existent directories and readable, please'
				);

			$path = self::$moduleDirectory.$name.EXT_MOD;
			
			if (!is_readable($path))
				throw new ObjectNotFoundException();

			require_once $path;
		}
		
		public static function setTemplateDirectory($dir)
		{
			self::$templateDirectory = $dir;
		}
		
		public static function getTemplateDirectory()
		{
			return self::$templateDirectory;
		}
		
		public static function setModuleDirectory($dir)
		{
			self::$moduleDirectory = $dir;
		}
	}
?>