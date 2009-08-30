<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * System-wide factory with predefined DB instance.
	 * 
	 * @ingroup DB
	**/
	final class DBFactory extends StaticFactory
	{
		private static $defaultDB = null;
		
		public static function setDefaultInstance(DB $db)
		{
			Assert::isTrue($db->isConnected());
			
			self::$defaultDB = $db;
		}
	
		public static function &getDefaultInstance()
		{
			if (self::$defaultDB === null)
				DBFactory::connect();
			
			return self::$defaultDB;
		}

		public static function getCustomInstance(
			$userName, $passWord, $host, $base = null, $connector = null
		)
		{
			if (!$connector) {
				if (!defined('DB_CLASS'))
					throw new WrongStateException(
						'you should define DB_CLASS in your config file'
					);
				
				$connector = DB_CLASS;
			}

			$db = new $connector;
			$db->connect($userName, $passWord, $host, $base);

			if (defined('DEFAULT_ENCODING')) {
				try {
					$db->setEncoding(DEFAULT_ENCODING);
				} catch (UnsupportedMethodException $ume) {/*_*/}
			}

			return $db;
		}
		
		private static function connect()
		{
			if (!self::$defaultDB)
				self::$defaultDB =
					DBFactory::getCustomInstance(
						DB_USER,
						DB_PASS,
						DB_HOST,
						DB_BASE
					);
			
			return DBFactory::$defaultDB;
		}
	}
?>