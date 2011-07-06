<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
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

		public static function makeTempFile(
			$where = 'file-utils/', $prefix = '', $mkdirMode = 0700
		)
		{
			$directory = ONPHP_TEMP_PATH.$where;

			if (!is_writable($directory))
				if (!mkdir($directory, $mkdirMode, true))
					throw new WrongArgumentException(
						"can not write to '{$directory}'"
					);

			$result = tempnam($directory, $prefix);

			if ($result === false)
				throw new WrongArgumentException(
					'failed to create temp file in '.$directory
				);
			
			return $result;
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
		
		public static function makeUniqueName($fileName)
		{
			$extensionPosition = strrpos($fileName, '.');
			
			return
				substr($fileName, 0, $extensionPosition)
				.'_'.uniqid()
				.substr($fileName, $extensionPosition);
		}

		public static function makeUniqueLatinName($originalName)
		{
			$extension = substr($originalName, strrpos($originalName, '.'));
			
			Assert::isEqual(
				(preg_match('/\.[^0-9a-zA-Z]/', $extension)),
				0,
				'I don\'t know how to work with this extension: '.$extension
			);
			
			return time().'_'.uniqid().$extension;

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
				if (!is_dir($directory)) {
					throw new WrongStateException(
						'Unable to found target directory: '.$directory
					);
				}

				$directoryIterator = new DirectoryIterator($directory);
				
				foreach ($directoryIterator as $file) {
					if ($file->isDot())
						continue;
					
					if ($file->isDir())
						self::removeDirectory($file->getPathname(), $recursive);
					elseif (!unlink($file->getPathname()))
						throw new WrongStateException(
							"cannot unlink {$file->getPathname()}"
						);
				}
				
				try {
					rmdir($directory);
				} catch (BaseException $e) {
					throw new WrongStateException(
						"cannot unlink {$directory}, though it should be empty now"
					);
				}
			}
		}
		
		public static function upload($source, $target)
		{
			if (
				is_readable($source)
				&& is_writable(pathinfo($target, PATHINFO_DIRNAME))
			)
				return move_uploaded_file($source, $target);
			
			throw new WrongArgumentException(
				"can not move {$source} to {$target}"
			);
		}
		
		public static function move($source, $target)
		{
			if (
				is_readable($source)
				&& is_writable(pathinfo($target, PATHINFO_DIRNAME))
			)
				return rename($source, $target);
			
			throw new WrongArgumentException(
				"can not move {$source} to {$target}"
			);
		}
		
		public static function unlink($filePath)
		{
			if (
				file_exists($filePath)
				&& is_writable(pathinfo($filePath, PATHINFO_DIRNAME))
			)
				return unlink($filePath);
			
			throw new WrongArgumentException(
				"can not unlink {$filePath}"
			);
		}
	}
?>
