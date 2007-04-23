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
/* $Id$ */

	class PackageConfiguration
	{
		const PATH_CLASSES		= 'classes';
		const PATH_TEMPLATES	= 'templates';
		const PATH_CONTROLLERS	= 'controllers';

		private $baseDirectory	= null;
		
		// must be relative to baseDirectory
		private $classPaths			= array();
		private $controllerPaths	= array();
		private $templatePaths		= array();
		
		/**
		 * @return PackageConfiguration
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public static function createDefaultClassPath()
		{
			return self::create()->
				addClassPath('Business')->
				addClassPath('DAOs')->
				addClassPath('Proto')->
				addClassPath('Auto'.DIRECTORY_SEPARATOR.'Business')->
				addClassPath('Auto'.DIRECTORY_SEPARATOR.'DAOs')->
				addClassPath('Auto'.DIRECTORY_SEPARATOR.'Proto');
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public function setBaseDirectory($baseDirectory)
		{
			$this->baseDirectory = $baseDirectory;

			return $this;
		}

		public function getBaseDirectory()
		{
			return $this->baseDirectory;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public function addClassPath($path)
		{
			$this->classPaths[] = $this->normalizePath($path);

			return $this;
		}

		public function getClassPaths()
		{
			return $this->classPaths;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public function addControllerPath($path)
		{
			$this->controllerPaths[] = $this->normalizePath($path);

			return $this;
		}

		public function getControllerPaths()
		{
			if (!isset($this->controllerPaths))
				throw new WrongStateException(
					'package does not have a business logic'
				);
			
			return $this->controllerPaths;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public function addTemplatePath($path)
		{
			Assert::isTrue(
				isset($this->controllerPaths),
				'package does not have a presentation logic'
			);

			$this->templatePaths[] = $this->normalizePath($path);

			return $this;
		}

		public function getTemplatePaths()
		{
			return $this->templatePaths;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public function setupViewResolver(
			PhpChainedViewResolver $resolver, BaseMarkupLanguage $language,
			$area = null
		)
		{
			Assert::isTrue(isset($this->basePath));

			// FIXME: unused variable - $templatePath		
			foreach ($this->templatePaths as $templatePath) {
				$resolver->addPrefix(
					$this->basePath.self::PATH_TEMPLATES.DIRECTORY_SEPARATOR
					.$language->getName().DIRECTORY_SEPARATOR
					.(
						$area
						? $area.DIRECTORY_SEPARATOR
						: null
					)
				);
			}

			return $this;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		// TODO: move to Application?
		public function setAutoIncludeControllerPaths($locationArea)
		{
			Assert::isTrue(isset($this->basePath));

			$includePath = get_include_path().PATH_SEPARATOR;

			foreach ($this->controllerPaths as $controllerPath) {
				$includePath .=
					$this->basePath.self::PATH_CONTROLLERS.DIRECTORY_SEPARATOR
					.$locationArea.DIRECTORY_SEPARATOR
					.$controllerPath.PATH_SEPARATOR;
			}

			set_include_path($includePath);

			return $this;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		// TODO: move to Application?
		public function setAutoincludeClassPaths()
		{
			Assert::isTrue(isset($this->basePath));

			$includePath = get_include_path().PATH_SEPARATOR;

			foreach ($this->classPaths as $classPath) {
				$includePath .=
					$this->basePath.self::PATH_CLASSES.DIRECTORY_SEPARATOR
					.$classPath.PATH_SEPARATOR;
			}

			set_include_path($includePath);

			return $this;
		}

		// TODO: check if we have imported paths or not?
		public function isControllerAvailable($locationArea, $controllerName)
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
		
		/**
		 * @return PackageConfiguration
		**/
		public function importOneClass($qualifiedName)
		{
			Assert::isTrue(isset($this->basePath));

			$parts = split('.', $qualifiedName);

			$className = array_pop($parts);
			$classPath = join(DIRECTORY_SEPARATOR, $parts).DIRECTORY_SEPARATOR;

			if (!in_array($classPath, $this->classPaths))
				throw new WrongArgumentException(
					"class path {{$classPath}} does not defined"
				);

			$classFile = $this->basePath.$classPath.$className.EXT_CLASS;

			if (!is_readable($classFile))
				throw new WrongArgumentException(
					"class file {{$classPath}} not found"
				);

			require $classFile;

			return $this;
		}

		private function normalizePath($path)
		{
			$result = $this->normalizeDirectory($path);

			if (substr($result, 1, 1) === DIRECTORY_SEPARATOR)
				$result = substr($result, 1);
			
			return $result;
		}

		private function normalizeDirectory($directory)
		{
			$result = preg_replace('~/~', DIRECTORY_SEPARATOR, $directory);

			if (substr($result, -1, 1) !== DIRECTORY_SEPARATOR)
				$result .= DIRECTORY_SEPARATOR;

			return $result;
		}
	}
?>