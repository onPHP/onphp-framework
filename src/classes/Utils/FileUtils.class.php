<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Sveta Smirnova                             *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$*/

	/**
	 * Contains utilities for files handling
	 * 
	 * 
	 * @package		Utils
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @version		1.0
	 * @copyright	2005
	**/
	class FileUtils
	{
		/**
		 * Recursive walk through $dir and converts line endings of all files instead
		 * having extentions founded in $ignore array
		 * 
		 * @param	string		path to source directory
		 * @param	array		extentions for ignoring
		 * @param	string		from which line endings convert
		 * @param	string		to which line endings convert
		 * @access	public
		 * @return	integer		quantity of handled files
		**/
		public static function convertLineEndings($dir, $ignore, $from = "\r\n", $to = "\n")
		{
			$converted = 0;

			if (!is_dir($dir) || !is_readable($dir)) {
				throw new WrongArgumentException();
			}

			$files = scandir($dir);

			foreach ($files as $file) {
				if ('.' != $file && '..' != $file &&
					!in_array(substr($file, strrpos($file, '.')), $ignore, true))
				{
					if (is_dir($path = $dir . DIRECTORY_SEPARATOR . $file)) {
						$converted += FileUtils::convertLineEndings($path, $ignore, $from, $to);
					} else {
						file_put_contents($path, preg_replace("/$from/", $to, file_get_contents($path)));
						$converted ++;
					}
				}
			}

			return $converted;
		}
	}
?>