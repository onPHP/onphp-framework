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
	
	class PackageConfiguration
	{
		private $classPaths			= array();
		
		private $container			= false;
		private $controllers		= false;
		private $templates			= false;
		
		private $packages			= array();
		
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
		public static function createContainer()
		{
			$result = self::create();
			
			$result->container = true;
			
			return $result;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public static function createMetaClassPaths()
		{
			$result =
				self::create()->
				addClassPath('Business')->
				addClassPath('DAOs')->
				addClassPath('Proto')->
				addClassPath('Auto'.DIRECTORY_SEPARATOR.'Business')->
				addClassPath('Auto'.DIRECTORY_SEPARATOR.'DAOs')->
				addClassPath('Auto'.DIRECTORY_SEPARATOR.'Proto')->
				addClassPath('Auto'.DIRECTORY_SEPARATOR.'DTOs');
			
			$result->container = false;
			
			return $result;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public static function createApplicationPaths()
		{
			return
				self::createMetaClassPaths()->
				setControllers(true)->
				setTemplates(true);
		}
		
		public function isContainer()
		{
			return $this->container;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public function addClassPath($path)
		{
			$this->classPaths[] = PathResolver::normalizePath($path);
			
			return $this;
		}
		
		public function getClassPaths()
		{
			return $this->classPaths;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public function setControllers($controllers)
		{
			Assert::isBoolean($controllers);
			
			Assert::isFalse(
				$this->container,
				'container cannot have controllers'
			);
			
			$this->controllers = $controllers;
			
			return $this;
		}
		
		public function hasControllers()
		{
			return $this->controllers;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public function setTemplates($templates)
		{
			Assert::isBoolean($templates);
			
			Assert::isFalse(
				$this->container,
				'container cannot have templates'
			);
			
			$this->templates = $templates;
			
			return $this;
		}
		
		public function hasTemplates()
		{
			return $this->templates;
		}
		
		/**
		 * @return PackageConfiguration
		**/
		public function addPackage(
			$name, /* PackageConfiguration */ $configuration = null
		)
		{
			if ($configuration)
				Assert::isTrue($configuration instanceof PackageConfiguration);
			
			Assert::isTrue(
				$this->container,
				'only container can have subpackages'
			);
			
			if (isset($this->packages[$name]))
				throw new WrongArgumentException(
					"package with name '{$name}' already exists"
				);
			
			$this->packages[$name] = $configuration;
			
			return $this;
		}
		
		public function getPackages()
		{
			return $this->packages;
		}
	}
?>