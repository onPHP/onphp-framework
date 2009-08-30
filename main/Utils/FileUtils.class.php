<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Utilities for file handling.
	 * 
	 * @ingroup Utils
	**/
	final class FileUtils extends StaticFactory
	{
		/**
		 * Recursively walks through $dir and converts line
		 * endings of all but listed in $ignore files.
		**/
		public static function convertLineEndings(
			$dir, $ignore, $from = "\r\n", $to = "\n"
		)
		{
			$converted = 0;

			if (!is_dir($dir) || !is_readable($dir)) {
				throw new WrongArgumentException();
			}

			$files = scandir($dir);

			foreach ($files as $file) {
				if (
					'.' != $file
					&& '..' != $file
					&&
					!in_array(
						substr($file, strrpos($file, '.')), $ignore, true
					)
				) {
					if (is_dir($path = $dir . DIRECTORY_SEPARATOR . $file)) {
						$converted += self::convertLineEndings(
							$path, $ignore, $from, $to
						);
					} else {
						file_put_contents(
							$path,
							preg_replace(
								"/$from/",
								$to,
								file_get_contents($path)
							)
						);
						
						++$converted;
					}
				}
			}

			return $converted;
		}
	}
?>