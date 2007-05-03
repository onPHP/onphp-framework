<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
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
		
		public static function init(BaseApplication $application)
		{
			Assert::isNull(self::$application);
			
			self::$application = $application;
		}
	}
?>