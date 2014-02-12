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

			try {
				$realPath = AutoloaderShmop::get($desiredName);
			} catch(Exception $e) {
				$realPath = null;
			}

			if( isset($realPath) && !is_null($realPath) ) {
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

		/**
		 * Default shm key, unique enough
		 * @const string
		 */
		const DEFAULT_SHM_KEY = 'onphp-autoloader-shmop-cache';

		/**
		 * Default shared memory segment size (16 MB)
		 * @const int
		 */
		const DEFAULT_SHM_SIZE = 16777216;

		/**
		 * Size of field which stores data array size
		 * Starts at zero byte of shm segment
		 * @const int
		 */
		const SHM_DATA_OFFSET = 24;

		/**
		 * Shared segment id
		 * @var int|null
		 */
		protected static $shm = null;

		/**
		 * Temp storage
		 * @var array|null
		 */
		protected static $storage = null;

		/**
		 * Get value from storage
		 * @param string $key
		 * @return string|null
		 */
		public static function get($key) {
			if(!self::check()) return null;
			return self::has($key) ? self::$storage[$key] : null;
		}

		/**
		 * Store data to storage
		 * @param string $key
		 * @param string $value
		 * @return string
		 */
		public static function set($key, $value) {
			if(!self::check()) return null;
			self::$storage[$key] = $value;
			return $value;
		}

		/**
		 * Check if storage has given key
		 * @param string $key
		 * @return bool
		 */
		public static function has($key) {
			if(!self::check()) return null;
			return isset(self::$storage[$key]);
		}

		public static function save() {
			if(!self::check()) return null;
			$size = shmop_write(self::$shm, json_encode(self::$storage), self::SHM_DATA_OFFSET);
			return self::updateSize($size);
		}

		public static function clean() {
			self::$storage = array();
			self::save();
		}

		protected static function check() {
			if( is_null(self::$shm) && is_null(self::$storage) ) {
				self::initSegment();
				self::load();
			}
			return self::$shm;
		}

		/**
		 * @return int|null
		 * @throws RuntimeException
		 */
		protected static function initSegment() {
			if( is_null(self::$shm) ) {
				$identifier = crc32( defined('ONPHP_CLASS_CACHE_KEY') ? ONPHP_CLASS_CACHE_KEY : self::DEFAULT_SHM_KEY );

				// Attempt to open shm segment
				self::$shm = @shmop_open($identifier, 'w', 0777, self::DEFAULT_SHM_SIZE);

				// If segment doesn't exist init new segment
				if (false === self::$shm) {
					self::$shm = shmop_open($identifier, 'c', 0777, self::DEFAULT_SHM_SIZE);

					if (false === self::$shm) {
						throw new RuntimeException('Unable to create shared memory segment with key: '.$identifier);
					}

					self::updateSize(0);
				}
			}
			return self::$shm;
		}

		protected static function load() {
			$size = intval(shmop_read(self::$shm, 0, self::SHM_DATA_OFFSET));
			if( $size === 0 ) {
				self::$storage = array();
			} else {
				$data = shmop_read(self::$shm, self::SHM_DATA_OFFSET, $size);
				self::$storage = json_decode($data, true);
			}

			return $size;
		}

		/**
		 * Update size field
		 * @param int size
		 * @return bool
		 */
		protected static function updateSize($size) {
			$size = sprintf('%' . self::SHM_DATA_OFFSET . 'd', intval($size));
			return shmop_write(self::$shm, $size, 0);
		}
	}

?>