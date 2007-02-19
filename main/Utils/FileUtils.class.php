<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Sveta Smirnova                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$*/

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

		public static function makeTempDirectory(
			$where = 'file-utils/', $prefix = '', $mode = 0700
		)
		{
			$directory = ONPHP_TEMP_PATH.$where;

			if (substr($directory, -1) != DIRECTORY_SEPARATOR)
				$directory .= DIRECTORY_SEPARATOR;

			$attempts = 42;	// it's more than enough ;)

			do {
				--$attempts;
				$path = $directory.$prefix.mt_rand();
			} while (
				!mkdir($path, $mode, true)
				&& $attempts > 0
				// not to rape fs
				&& !usleep(100)
			);

			if ($attempts == 0)
				throw new WrongArgumentException(
					'failed to create subdirectory in '.$directory
				);
			
			return $path;
		}

		/* void */ public static function removeDirectory($directory, $recursive = false)
		{
			if (!$recursive) {
				try {
					rmdir($directory);
				} catch (BaseException $e) {
					throw new WrongArgumentException($e->getMessage());
				}
			} else {
				if (!$handle = opendir($directory))
					throw new WrongArgumentException(
						'cannot read directory '.$directory
					);

				while (($item = readdir($handle)) !== false) {
					if ($item == '.' || $item == '..')
						continue;

					$path = $directory.DIRECTORY_SEPARATOR.$item;

					if (is_dir($path))
						self::removeDirectory($path);
					elseif (!unlink($path))
						throw new WrongStateException(
							"cannot unlink {$path}"
						);
				}

				closedir($handle);
				
				try {
					rmdir($directory);
				} catch (BaseException $e) {
					throw new WrongStateException(
						"cannot unlink {$directory}, though it should be empty now"
					);
				}
			}
		}
	}
?>