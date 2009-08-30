<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	class PackageManager extends Singleton implements Instantiatable
	{
		const CONFIGURATION_SCRIPT	= 'package.inc.php';
		
		private $packageResolvers	= array();
		
		private $imported			= array();
		
		/**
		 * @return PackageManager
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return PackageManager
		**/
		public function addPackage(
			$qualifiedName, $basePath,
			/* PackageConfiguration */ $configuration = null
		)
		{
			if (isset($this->packages[$qualifiedName]))
				throw new WrongArgumentException(
					"package with name '{$qualifiedName}' already exists"
				);
			
			$basePath = PathResolver::normalizeDirectory($basePath);
			
			if (!$configuration)
				$configuration =
					$this->getConfiguration(
						$basePath.self::CONFIGURATION_SCRIPT
					);
			
			$this->packageResolvers[$qualifiedName] =
				new PathResolver($basePath, $configuration);
			
			if ($configuration->isContainer()) {
				foreach (
					$configuration->getPackages() as $name => $subConfiguration
				) {
					$this->addPackage(
						$qualifiedName.'.'.$name,
						$basePath.$name,
						$subConfiguration
					);
				}
			}
			
			return $this;
		}
		
		/**
		 * @return PackageManager
		**/
		public function import($qualifiedName)
		{
			Assert::isFalse(
				isset($this->imported[$qualifiedName]),
				"already imported package '{$qualifiedName}'"
			);
			
			$parts = explode('.', $qualifiedName);
			
			$packageResolver = null;
			
			$classParts = array();
			
			while ($parts) {
				$searchName = implode('.', $parts);
				
				if (isset($this->packageResolvers[$searchName])) {
					$packageResolver = $this->packageResolvers[$searchName];
					break;
				}
				
				array_unshift($classParts, array_pop($parts));
			}
			
			if (!$packageResolver)
				throw new WrongArgumentException(
					"package for '{$qualifiedName}' not found"
				);
			
			if (!$classParts) {
				$packageResolver->includeClassPaths();
				
				$this->imported[$qualifiedName] = $packageResolver;
			} else
				$packageResolver->importOneClass(implode('.', $classParts));
			
			return $this;
		}
		
		public function getImportedList()
		{
			return $this->imported;
		}
		
		private function getConfiguration($configurationScript)
		{
			$result = include $configurationScript;
			
			if (!($result instanceof PackageConfiguration))
				throw new WrongArgumentException(
					"config '{$configurationScript}'"
					." must return valid configuration"
				);
			
			return $result;
		}
	}
?>