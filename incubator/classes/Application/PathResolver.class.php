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

			$includePaths = array();;

			foreach ($this->configuration->getClassPaths() as $classPath) {
				$includePath[] = $baseClassPath.$classPath;
			}

			Application::me()->addIncludePaths($includePath);

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
					"class path '{$classPath}' does not defined"
				);

			$classFile = $baseDirectory.$classPath.$className.EXT_CLASS;

			if (!is_readable($classFile))
				throw new WrongArgumentException(
					"class file '{$classPath}' not found"
				);

			$this->requireClass($classFile);

			return $this;
		}

		public function getTemplatesPath(BaseMarkupLanguage $language)
		{
			Assert::isFalse(
				$this->configuration->isContainer(),
				'containers do not have templates'
			);

			return
				$this->basePath.self::PATH_TEMPLATES.DIRECTORY_SEPARATOR
				.$language->getName().DIRECTORY_SEPARATOR;
		}

		public function getControllersPath($locationArea)
		{
			Assert::isFalse(
				$this->configuration->isContainer(),
				'containers do not have controllers'
			);
		}

		// TODO: check if we have imported paths or not?
		public function isControllerExists($locationArea, $controllerName)
		{
			Assert::isTrue(isset($this->basePath));

			foreach ($this->controllerPaths as $controllerPath) {
				if (
					is_readable(
						$this->basePath.self::PATH_CONTROLLERS.DIRECTORY_SEPARATOR
						.$locationArea.DIRECTORY_SEPARATOR
						.$controllerPath.$controllerName.EXT_CLASS
					)
				)
					return true;
			}

			return false;
		}

		/* void */ private function requireClass($classFile)
		{
			require $classFile;
		}
	}
?>