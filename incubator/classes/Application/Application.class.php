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

	class Application extends Singleton implements Instantiatable
	{
		const CSS_PATH			= 'css';
		const IMG_PATH			= 'img';

		const AREA_HOLDER		= 'area';

		private $name			= 'stdApp';

		private $locations		= null;

		private $location		= null;
		private $locationArea	= null;

		private $pathResolver	= null;

		private $actualDomain	= null;

		private $markup			= null;

		private $area			= null;
		private $queryString	= null;

		/**
		 * @return Application
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		protected function __construct()
		{
			$this->locations = LocationSettings::create();
		}
		
		/**
		 * @return Application
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
		 * @return Application
		**/
		public function setLocations(LocationSettings $locations)
		{
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
		 * @return Application
		**/
		public function setPathResolver($pathResolver)
		{
			$this->pathResolver = $pathResolver;

			return $this;
		}

		/**
		 * @return Application
		**/
		public function setupIncludePaths()
		{
			$this->pathResolver->includeClassPaths();

			return $this;
		}

		/**
		 * @return Application
		**/
		public function resideInWeb()
		{
			return $this->reside(LocationSettings::WEB);
		}
		
		/**
		 * @return Application
		**/
		public function resideInWap()
		{
			return $this->reside(LocationSettings::WAP);
		}
		
		/**
		 * @return Application
		**/
		public function resideInAdmin()
		{
			return $this->reside(LocationSettings::ADMIN);
		}
		
		/**
		 * @return Application
		**/
		public function resideInSoap()
		{
			return $this->reside(LocationSettings::SOAP);
		}
		
		/**
		 * @return Application
		**/
		public function reside($locationArea)
		{
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
						$pathResolver->getControllersPath(
							$this->locationArea
						);
			}

			$this->addIncludePaths($includePaths);
			
			return $this;
		}
		
		public function getLocationArea()
		{
			if (!$this->locationArea)
				throw new WrongArgumentException(
					'application does not reside anywhere'
				);

			return $this->locationArea;
		}
		
		public function getLocation()
		{
			if (!$this->location)
				throw new WrongArgumentException(
					'application does not reside anywhere'
				);

			return $this->location;
		}
		
		/**
		 * @return Application
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
		 * @return Application
		**/
		public function setupViewResolver(PhpChainedViewResolver $viewResolver)
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
						$pathResolver->getTemplatesPath(
							$this->locationArea, $this->markup
						)
					);
			}

			return $this;
		}
		
		/**
		 * @return Application
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
					isset($getVars[self::AREA_HOLDER])
					&& $pathResolver->isControllerExists(
						$this->locationArea, $getVars[self::AREA_HOLDER]
					)
				) {
					$controllerName = $getVars[self::AREA_HOLDER];
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

		/**
		 * @return Application
		**/
		public function addIncludePaths($paths)
		{
			Assert::isArray($paths);
			
			set_include_path(
				get_include_path().PATH_SEPARATOR.implode(PATH_SEPARATOR, $paths)
			);

			return $this;
		}

		/**
		 * @return Application
		**/
		public function setQueryString($queryString)
		{
			$this->queryString = $queryString;
		}

		public function getQueryString()
		{
			return $this->queryString;
		}

		public function url()
		{
			if ($this->queryString)
				return $this->baseUrl().'?'.$this->queryString;

			return $this->getLocation()->getUrl();
		}

		public function baseUrl()
		{
			return $this->getLocation()->getBaseUrl();
		}

		public function basePath()
		{
			return $this->getLocation()->getPath();
		}

		public function areaUrl($area = null)
		{
			if (!$area)
				$actualArea = $this->area;
			else
				$actualArea = $area;

			return
				$this->getLocation()->getPath()
				.'?'.self::AREA_HOLDER.'='.$actualArea;
		}

		public function imgPath()
		{
			$result = $this->baseUrl().self::IMG.'/';

			if ($this->markup)
				$result .= $this->markup->getCommonName().'/';

			return $result;
		}

		public function cssPath()
		{
			$result = $this->baseUrl().self::CSS.'/';

			if ($this->markup)
				$result .= $this->markup->getCommonName().'/';

			return $result;
		}
		
		/**
		 * @return Application
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

		public function getWebLocation()
		{
			return $this->locations->getWeb();
		}

		public function getWebUrl()
		{
			return $this->getWebLocation()->getUrl();
		}

		public function getWapLocation()
		{
			return $this->locations->getWap();
		}

		public function getWapUrl()
		{
			return $this->getWapLocation()->getUrl();
		}

		public function getAdminLocation()
		{
			return $this->locations->getAdmin();
		}

		public function getAdminUrl()
		{
			return $this->getAdminLocation()->getUrl();
		}

		public function getSoapLocation()
		{
			return $this->locations->getSoap();
		}

		public function getSoapUrl()
		{
			return $this->getSoapLocation()->getUrl();
		}
	}
?>