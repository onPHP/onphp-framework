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
	
	class BaseApplication
	{
		protected $name			= 'stdApp';
		
		protected $areaHolder	= 'area';
		
		protected $locations	= null;
		
		protected $location		= null;
		protected $locationArea	= null;
		
		protected $pathResolver	= null;
		
		protected $actualDomain	= null;
		
		protected $markup		= null;
		
		protected $area			= null;
		protected $queryString	= null;
		
		protected $staticStorages	= array();
		
		protected $neighbors	= array();
		
		/**
		 * @return BaseApplication
		**/
		public static function create()
		{
			return new self;
		}
		
		public function url()
		{
			return
				$this->baseUrl()
				.(
					$this->queryString
					? '?'.$this->queryString
					: null
				);
		}
		
		public function baseUrl()
		{
			return $this->getLocation()->getUrl();
		}
		
		public function basePath()
		{
			return $this->getLocation()->getPath();
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
		public function getNeighbor($neighbor)
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
		public function setPathResolver($pathResolver)
		{
			$this->pathResolver = $pathResolver;
			
			return $this;
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
					"application already resides at {{$this->area}}"
				);
			
			$this->location = $this->locations->get($locationArea);
			$this->locationArea = $locationArea;
			
			$pathResolvers = PackageManager::me()->getImportedList();
			
			array_unshift($pathResolvers, $this->pathResolver);
			
			$includePaths = array();
			
			foreach ($pathResolvers as $pathResolver) {
				if ($pathResolver->getConfiguration()->hasControllers())
					$includePaths[] =
						$pathResolver->getControllersPath();
			}
			
			Application::addIncludePaths($includePaths);
			
			return $this;
		}
		
		public function getLocationArea()
		{
			return $this->locationArea;
		}
		
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
		 * @return BaseApplication
		**/
		public function getController(HttpRequest $request, $defaultName)
		{
			if (!isset($this->locationArea))
				throw new WrongStateException(
					'first, reside me in someplace'
				);
			
			$controllerName = $defaultName;
			
			$getVars = $request->getGet();
		
			$pathResolvers = PackageManager::me()->getImportedList();
			
			array_unshift($pathResolvers, $this->pathResolver);
			
			foreach ($pathResolvers as $pathResolver) {
				if (
					isset($getVars[$this->areaHolder])
					&& $pathResolver->isControllerExists(
						$getVars[$this->areaHolder]
					)
				) {
					$controllerName = $getVars[$this->areaHolder];
					break;
				}
			}
			
			$result = new $controllerName;
			
			$this->area = $controllerName;
			
			return $result;
		}
		
		public function getArea()
		{
			return $this->area;
		}
		
		public function getAreaUrl($area = null)
		{
			if (!$area)
				$actualArea = $this->area;
			else
				$actualArea = $area;
			
			return
				$this->getLocation()->getPath()
				.'?'.$this->areaHolder.'='.$actualArea;
		}
		
		/**
		 * @return BaseApplication
		**/
		public function setQueryString($queryString)
		{
			$this->queryString = $queryString;
			
			return $this;
		}
		
		public function getQueryString()
		{
			return $this->queryString;
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