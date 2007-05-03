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
	
	class CommonApplication extends BaseApplication
	{
		const CSS_PATH			= 'css';
		const IMG_PATH			= 'img';
		
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
		public function resideInWeb()
		{
			return $this->reside(CommonLocationSettings::WEB);
		}
		
		/**
		 * @return BaseApplication
		**/
		public function resideInWap()
		{
			return $this->reside(CommonLocationSettings::WAP);
		}
		
		/**
		 * @return BaseApplication
		**/
		public function resideInAdmin()
		{
			return $this->reside(CommonLocationSettings::ADMIN);
		}
		
		/**
		 * @return BaseApplication
		**/
		public function resideInSoap()
		{
			return $this->reside(CommonLocationSettings::SOAP);
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
		
		public function imgExt()
		{
			if ($this->markup instanceof WmlLanguage)
				$result .= $this->markup->getCommonName().'/';
			
			return $result;
		}
		
		public function cssPath()
		{
			$result = $this->baseUrl().self::CSS_PATH.'/';
			
			if ($this->markup)
				$result .= $this->markup->getCommonName().'/';
			
			return $result;
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