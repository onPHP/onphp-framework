<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * System-wide factory with predefined DB instance.
	**/
	final class DBFactory extends StaticFactory
	{
		private static $defaultDB = null;
	
		public static function &getDefaultInstance()
		{
			if (self::$defaultDB === null)
				DBFactory::connect();
			
			return self::$defaultDB;
		}

		/**
		 * Weird documentation example.
		 * 
		 * @return DB resource
		 * @param string guess, what's this?
		 * @param string see above
		 * @param string host:port
		 * @param string default database connect to
		 * @desc BOVM goes here..
		**/
		public static function getCustomInstance(
			$userName, $passWord, $host, $base = null
		)
		{
			if (!defined('DB_CLASS'))
				throw new WrongStateException(
					'you should define DB_CLASS in your config file'
				);

			$dbClass = DB_CLASS;

			$db = new $dbClass;
			$db->connect($userName, $passWord, $host, $base);

			try {
				$db->setEncoding(DEFAULT_ENCODING);
			} catch (UnsupportedMethodException $ume) {/*_*/}

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