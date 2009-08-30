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
	
	class BaseApplication
	{
		protected $name			= 'stdApp';
		
		protected $locations	= null;
		
		protected $location		= null;
		protected $locationArea	= null;
		
		protected $pathResolver	= null;
		
		protected $actualDomain	= null;
		
		protected $markup		= null;
		
		protected $navigationArea	= null;
		
		protected $staticStorages	= array();
		
		protected $neighbors	= array();
		
		/**
		 * @return BaseApplication
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return BaseApplication
		**/
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return BaseApplication
		**/
		public function setLocations(LocationSettings $locations)
		{
			Assert::isNull($this->locations);
			
			$this->locations = $locations;
			
			return $this;
		}
		
		/**
		 * @return LocationSettings
		**/
		public function getLocationSettings()
		{
			return $this->locations;
		}
		
		/**
		 * @return BaseApplication
		**/
		public function addNeighbor($name, LocationSettings $neighbor)
		{
			$this->neighbors[$name] = $neighbor;
			
			return $this;
		}
		
		/**
		 * @return StaticStorage
		**/
		public function getNeighbor($name)
		{
			if (!isset($this->neighbor[$name]))
				throw new WrongArgumentException(
					"knows nothing about neighbor application '{$name}'"
				);
			
			return $this->neighbors[$name];
		}
		
		/**
		 * @return BaseApplication
		**/
		public function setPathResolver(PathResolver $pathResolver)
		{
			$this->pathResolver = $pathResolver;
			
			return $this;
		}
		
		/**
		 * @return PathResolver
		**/
		public function getPathResolver()
		{
			return $this->pathResolver;
		}
		
		/**
		 * @return BaseApplication
		**/
		public function setupIncludePaths()
		{
			$this->pathResolver->includeClassPaths();
			
			return $this;
		}
		
		/**
		 * @return BaseApplication
		**/
		public function reside($locationArea)
		{
			Assert::isNotNull($this->locations);
			
			if ($this->locationArea)
				throw new WrongArgumentException(
					"application already resides at {{$this->locationArea}}"
				);
			
			$this->location = $this->locations->get($locationArea);
			$this->locationArea = $locationArea;
			
			$pathResolvers = PackageManager::me()->getImportedList();
			
			array_unshift($pathResolvers, $this->pathResolver);
			
			$includePaths = array();
			
			foreach ($pathResolvers as $pathResolver) {
				if ($pathResolver->getConfiguration()->hasControllers())
					$includePaths[] = $pathResolver->getControllersPath();
			}
			
			Application::addIncludePaths($includePaths);
			
			return $this;
		}
		
		public function getLocationArea()
		{
			return $this->locationArea;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function getLocation()
		{
			return $this->location;
		}
		
		/**
		 * @return BaseApplication
		**/
		public function setMarkup(BaseMarkupLanguage $markup)
		{
			$this->markup = $markup;
			
			return $this;
		}
		
		/**
		 * @return BaseMarkupLanguage
		**/
		public function getMarkup()
		{
			return $this->markup;
		}
		
		/**
		 * @return BaseApplication
		**/
		public function setupViewResolver(MultiPrefixPhpViewResolver $viewResolver)
		{
			if (!isset($this->locationArea) || !isset($this->markup))
				throw new WrongStateException(
					'first, reside me in someplace and set the markup'
				);
			
			$pathResolvers = PackageManager::me()->getImportedList();
			
			array_unshift($pathResolvers, $this->pathResolver);
			
			foreach ($pathResolvers as $pathResolver) {
				if ($pathResolver->getConfiguration()->hasTemplates())
					$viewResolver->addPrefix(
						$pathResolver->getTemplatesPath()
					);
			}
			
			return $this;
		}
		
		/**
		 * @return Controller
		**/
		public function getController($defaultName)
		{
			if (!$this->locationArea)
				throw new WrongStateException(
					'first, reside me in someplace'
				);
			
			if (!$this->navigationArea)
				throw new WrongStateException(
					'first, navigate me somewhere'
				);
			
			$pathResolvers = PackageManager::me()->getImportedList();
			
			array_unshift($pathResolvers, $this->pathResolver);
			
			$controllerName = null;
			
			$areaName = $this->navigationArea->getName();
			
			if ($areaName) {
				foreach ($pathResolvers as $pathResolver) {
					if (
						$pathResolver->getConfiguration()->hasControllers()
						&& $pathResolver->isControllerExists($areaName)
					) {
						$controllerName = $areaName;
						break;
					}
				}
			}
			
			if (!$controllerName) {
				$controllerName = $defaultName;
				
				$this->navigationArea = new NavigationArea($controllerName);
			}
			
			$result = new $controllerName;
			
			Assert::isTrue($result instanceof Controller);
			
			return $result;
		}
		
		/**
		 * @return BaseApplication
		**/
		public function setNavigationArea(NavigationArea $navigationArea)
		{
			$this->navigationArea = $navigationArea;
			
			return $this;
		}
		
		/**
		 * @return NavigationArea
		**/
		public function getNavigationArea()
		{
			return $this->navigationArea;
		}
		
		public function url()
		{
			return $this->location->getNavigationUrl($this->navigationArea);
		}
		
		public function baseUrl()
		{
			return $this->getLocation()->getUrl();
		}
		
		public function basePath()
		{
			return $this->getLocation()->getPath();
		}
		
		public function areaUrl($name, $action = null, $model = null)
		{
			return $this->getNavigationUrl(
				new NavigationArea($name, $action, $model)
			);
		}
		
		public function getNavigationUrl(NavigationArea $navigationArea)
		{
			return $this->location->getNavigationUrl($navigationArea);
		}
		
		/**
		 * @return BaseApplication
		**/
		public function setActualDomain($actualDomain)
		{
			$this->actualDomain = $actualDomain;
			
			return $this;
		}
		
		public function getActualDomain()
		{
			return $this->actualDomain;
		}
		
		/**
		 * @return BaseApplication
		**/
		public function setStaticStorage($name, StaticStorage $storage)
		{
			$this->staticStorages[$name] = $storage;
			
			return $this;
		}
		
		/**
		 * @return StaticStorage
		**/
		public function getStaticStorage($name)
		{
			if (!isset($this->staticStorages[$name]))
				throw new WrongArgumentException(
					"knows nothing about storage '{$name}'"
				);
			
			return $this->staticStorages[$name];
		}
	}
?>