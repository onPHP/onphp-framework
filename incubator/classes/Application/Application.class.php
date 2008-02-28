<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	// TODO: see oemlib/trunk/Utils/Index.class.php
	// disadvantage: is's hard to insert some filter inside a chain
	final class Application extends StaticFactory implements Instantiatable
	{
		private static $application = null;
		
		/**
		 * @return BaseApplication
		**/
		public static function me()
		{
			Assert::isNotNull(self::$application);
			
			return self::$application;
		}
		
		/* void */ public static function init(BaseApplication $application)
		{
			Assert::isNull(self::$application);
			
			self::$application = $application;
		}
		
		/* void */ public static function addIncludePaths($paths)
		{
			Assert::isArray($paths);
			
			set_include_path(
				get_include_path().PATH_SEPARATOR.implode(PATH_SEPARATOR, $paths)
			);
		}
	}
?>