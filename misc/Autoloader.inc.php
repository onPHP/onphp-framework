<?php
/***************************************************************************
 *   Copyright (C) 2008-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	define('ONPHP_CLASS_CACHE_CHECKSUM', '__occc');
	
	abstract class Autoloader
	{
		public static function classPathCache($classname)
		{
			// numeric indexes for directories, literal indexes for classes
			static $cache 		= null;
			static $path 		= null;
			static $checksum 	= null;
			
			if (strpos($classname, "\0") !== false) {
				// we can not avoid fatal error in this case
				return /* void */;
			}
			
			$currentPath = get_include_path();
			
			if ($currentPath != $path) {
				$checksum = crc32($currentPath.ONPHP_VERSION);
				$path = $currentPath;
			}
			
			$cacheFile = ONPHP_CLASS_CACHE.$checksum.'.occ';
			
			if ($cache && ($cache[ONPHP_CLASS_CACHE_CHECKSUM] <> $checksum))
				$cache = null;
			
			if (!$cache) {
				try {
					$cache = unserialize(@file_get_contents($cacheFile, false));
				} catch (BaseException $e) {
					/* ignore */
				}
				
				if (isset($cache[$classname])) {
					try {
						include $cache[$cache[$classname]].$classname.EXT_CLASS;
						return /* void */;
					} catch (ClassNotFoundException $e) {
						throw $e;
					} catch (BaseException $e) {
						$cache = null;
					}
				}
			}
			
			if (!$cache) {
				$cache = array();
				$dirCount = 0;
				
				foreach (explode(PATH_SEPARATOR, get_include_path()) as $directory) {
					$cache[$dirCount] = realpath($directory).DIRECTORY_SEPARATOR;

					if ($paths = glob($cache[$dirCount].'*'.EXT_CLASS, GLOB_NOSORT)) {
						foreach ($paths as $class) {
							$class = basename($class, EXT_CLASS);
							
							// emulating include_path searching behaviour
							if (!isset($cache[$class]))
								$cache[$class] = $dirCount;
						}
					}
					
					++$dirCount;
				}
				
				$cache[ONPHP_CLASS_CACHE_CHECKSUM] = $checksum;
				
				if (
					is_writable(dirname($cacheFile))
					&& (
						!file_exists($cacheFile)
						|| is_writable($cacheFile)
					)
				)
					file_put_contents($cacheFile, serialize($cache));
			}
			
			if (isset($cache[$classname])) {
				$fileName = $cache[$cache[$classname]].$classname.EXT_CLASS;
				
				try {
					include $fileName;
				} catch (BaseException $e) {
					if (is_readable($fileName))
						// class compiling failed
						throw $e;
					else {
						// cache is not actual
						$cache[ONPHP_CLASS_CACHE_CHECKSUM] = null;
						self::classPathCache($classname);
					}
				}
			} else {
				// ok, last chance to find class in non-cached include_path
				self::noCache($classname);
				$cache[ONPHP_CLASS_CACHE_CHECKSUM] = null;
				return /* void */;
			}
		}
		
		public static function noCache($classname)
		{
			if (strpos($classname, "\0") !== false) {
				/* are you sane? */
				return;
			}

			// make namespaces work
			$classname = str_replace('\\', '/', $classname);

			$errors = array();
			foreach (array(EXT_CLASS, '.php') as $ext) {
				try {
					include $classname.$ext;
					return /* void */;
				} catch (BaseException $e) {
					$errors[] = $e->getMessage();
				}
			}

			if (!class_exists($classname)) {
				__autoload_failed($classname, 'class not found' . PHP_EOL . implode(PHP_EOL, $errors));
			}
		}
		
		public static function wholeClassCache($classname)
		{
			// we must duplicate this one here, otherwise any fail will be silent
			require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'BaseException'.EXT_MOD;
			
			static $path = null;
			static $checksum = null;
			static $included = array();
			
			if (strpos($classname, "\0") !== false) {
				/* are you sane? */
				return;
			}
			
			$currentPath = get_include_path();
			
			if ($currentPath != $path) {
				$checksum = crc32($currentPath.ONPHP_VERSION);
				$path = $currentPath;
			}
			
			$cacheFile = ONPHP_CLASS_CACHE.$checksum.'.occ';
			
			if (!isset($included[$cacheFile])) {
				try {
					include $cacheFile;
					$included[$cacheFile] = true;
				} catch (BaseException $e) {
					/* ignore */
				}
			}
			
			if (!class_exists($classname)) {
				static $pid = null;
				
				if (!$pid) {
					$pid = getmypid();
					
					register_shutdown_function(array('Autoloader', 'autoloadCleanup'));
				}
				
				try {
					$classPath = null;
					
					foreach (
						explode(PATH_SEPARATOR, get_include_path())
						as $directory
					) {
						$location = $directory.'/'.$classname.EXT_CLASS;
						
						if (is_readable($location)) {
							$classPath = $location;
							break;
						}
					}
					
					if (!$classPath)
						throw new BaseException('failed to find requested class');
					
					$class = file_get_contents($classPath);
					
					eval('?>'.$class);
				} catch (BaseException $e) {
					return __autoload_failed($classname, $e->getMessage());
				}
				
				file_put_contents($cacheFile.'-'.$pid, $class, FILE_APPEND);
				
				$included[$cacheFile] = true;
			}
		}
			
		public static function autoloadCleanup()
		{
			$pid = getmypid();
			
			if ($paths = glob(ONPHP_CLASS_CACHE.'*-'.$pid, GLOB_NOSORT)) {
				foreach ($paths as $file) {
					rename($file, ONPHP_CLASS_CACHE.basename($file, '-'.$pid));
				}
			}
		}

		public static function classPathSharedMemCache($classname) {
			if (strpos($classname, "\0") !== false) {
				/* are you sane? */
				return;
			}

			// make namespaces work
			$desiredName = str_replace('\\', '/', $classname);
			if($desiredName{0}=='/') {
				$desiredName = substr($desiredName, 1);
			}

			if( $realPath = AutoloaderShmop::get($desiredName) ) {
				include_once $realPath;
			} else {
				foreach (explode(PATH_SEPARATOR, get_include_path()) as $directory) {
					$directory = realpath($directory);
					foreach (array(EXT_CLASS, EXT_LIB) as $ext) {
						$realPath = $directory.DIRECTORY_SEPARATOR.$desiredName.$ext;
						if( $realPath && file_exists($realPath) && is_readable($realPath) ) {
							break;
						} else {
							$realPath = null;
						}
					}
					if( !is_null($realPath) ) {
						break;
					}
				}
				if( !is_null($realPath) ) {
					AutoloaderShmop::set($desiredName, $realPath);
					include_once $realPath;
				}
			}

			if (!class_exists($classname, false) && !interface_exists($classname, false)) {
				__autoload_failed($classname, 'class not found');
			}
		}
	}

	abstract class AutoloaderShmop {

		const INDEX_SEGMENT		= 23456789; // random int :)
		const SEGMENT_SIZE		= 4194304; // 128^3 * 2
		const EXPIRATION_TIME	= 86400; // 24 hours

		private static $attachedId = null;

		public static function get($classname) {
			if( !self::segment() ) {
				return null;
			}
			if( self::has($classname) ) {
				try {
					list($expires, $path) = shm_get_var(self::segment(), self::key($classname));
				} catch( Exception $e ) {
					$expires = 0;
					$path = null;
				}
				if( $expires<time() ) {
					self::drop($classname);
				}
				return $path;
			}
			return null;
		}

		public static function set($classname, $classpath) {
			if( !self::segment() ) {
				return false;
			}
			if( strlen($classpath) > 256 ) {
				throw new BaseException('Class path is too long (more than 256 symbols)');
			}
			if( self::has($classname) ) {
				self::drop($classname);
			}
			return shm_put_var(self::segment(), self::key($classname), array(0=>(time()+self::EXPIRATION_TIME), 1=>$classpath));
		}

		public static function drop($classname) {
			if( !self::segment() ) {
				return false;
			}
			return shm_remove_var(self::segment(), self::key($classname));
		}

		public static function has($classname) {
			if( !self::segment() ) {
				return false;
			}
			return shm_has_var(self::segment(), self::key($classname));
		}

		public static function clean() {
			if( !self::segment() ) {
				return false;
			}
			return shm_remove(self::segment());
		}

		private static function segment() {
			if( is_null(self::$attachedId) ) {
				try {
					self::$attachedId = @shm_attach(self::INDEX_SEGMENT, self::SEGMENT_SIZE, ONPHP_IPC_PERMS);
				} catch(Exception $e) {
					self::$attachedId = false;
				}

			}
			return self::$attachedId;
		}

		private static function key($classname) {
			return crc32('/cache/'.$classname);
		}

	}
?>