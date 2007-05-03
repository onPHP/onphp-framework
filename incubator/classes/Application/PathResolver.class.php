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
	
	class PathResolver
	{
		const PATH_CLASSES			= 'classes';
		const PATH_TEMPLATES		= 'templates';
		const PATH_CONTROLLERS		= 'controllers';
		
		private $baseDirectory	= null;
		private $configuration	= null;
		
		public function __construct(
			$baseDirectory, PackageConfiguration $configuration
		)
		{
			$this->baseDirectory = self::normalizeDirectory($baseDirectory);
			$this->configuration = $configuration;
		}
		
		public function getBaseDirectory()
		{
			return $this->baseDirectory;
		}
		
		public function getConfiguration()
		{
			return $this->configuration;
		}
		
		public static function normalizePath($path)
		{
			$result = str_replace('/', DIRECTORY_SEPARATOR, $path);
			
			if (substr($result, 1, 1) === DIRECTORY_SEPARATOR)
				$result = substr($result, 1);
			
			$result = self::normalizeDirectory($result);
			
			return $result;
		}
		
		public static function normalizeDirectory($directory)
		{
			$result = $directory;
			
			if (substr($result, -1, 1) !== DIRECTORY_SEPARATOR)
				$result .= DIRECTORY_SEPARATOR;
			
			return $result;
		}
		
		/**
		 * @return PathResolver
		**/
		public function includeClassPaths()
		{
			$baseClassPath = $this->baseDirectory;
			
			if (!$this->configuration->isContainer())
				$baseClassPath .= self::PATH_CLASSES.DIRECTORY_SEPARATOR;
			
			$includePaths = array();
			
			foreach ($this->configuration->getClassPaths() as $classPath) {
				$includePath[] = $baseClassPath.$classPath;
			}
			
			Application::addIncludePaths($includePath);
			
			return $this;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public function importOneClass($qualifiedName)
		{
			$baseClassPath = $this->baseDirectory;
			
			if (!$this->configuration->isContainer())
				$baseClassPath .= self::PATH_CLASSES.DIRECTORY_SEPARATOR;
			
			$parts = explode('.', $qualifiedName);
			
			$className = array_pop($parts);
			$classPath = implode(DIRECTORY_SEPARATOR, $parts).DIRECTORY_SEPARATOR;
			
			if (!in_array($classPath, $this->configuration->getClassPaths()))
				throw new WrongArgumentException(
					"class {$qualifiedName} not found at classpath '{$classPath}'"
				);
			
			$classFile = $this->baseDirectory.$classPath.$className.EXT_CLASS;
			
			if (!is_readable($classFile))
				throw new WrongArgumentException(
					"file '{$classFile}' for class '{$qualifiedName}' not found"
				);
			
			$this->requireClass($classFile);
			
			return $this;
		}
		
		public function getTemplatesPath($locationArea, BaseMarkupLanguage $language)
		{
			Assert::isFalse(
				$this->configuration->isContainer(),
				'containers do not have templates'
			);
			
			return
				$this->baseDirectory.$locationArea.DIRECTORY_SEPARATOR
				.self::PATH_TEMPLATES.DIRECTORY_SEPARATOR
				.$language->getCommonName().DIRECTORY_SEPARATOR;
		}
		
		public function getControllersPath($locationArea)
		{
			Assert::isFalse(
				$this->configuration->isContainer(),
				'containers do not have controllers'
			);
			
			return
				$this->baseDirectory.$locationArea.DIRECTORY_SEPARATOR
				.self::PATH_CONTROLLERS.DIRECTORY_SEPARATOR;
		}
		
		public function isControllerExists($locationArea, $controllerName)
		{
			if (
				is_readable(
					$this->getControllersPath($locationArea)
					.$controllerName.EXT_CLASS
				)
			)
				return true;
			
			return false;
		}
		
		/* void */ private function requireClass($classFile)
		{
			require $classFile;
		}
	}
?>