<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Basis for modules - business-logic containers.
	 * 
	 * @ingroup Module
	**/
	abstract class BaseModule
	{
		private $url		= null;

		private $parameters	= array();
		
		public function __construct()
		{
			$this->url = $_SERVER['PHP_SELF'].'?area='.$this->getName();
		}
		
		public function getUrl()
		{
			return $this->url;
		}

		public function setUrl($url)
		{
			$this->url = $url;
			
			return $this;
		}
		
		/* constructor stuff, cause' sometimes we won't initialize modules */
		public function init()
		{
			return $this;
		}

		public function dump()
		{
			if (!HeaderUtils::isHeaderSent())
				require $this->getTemplatePath();

			return $this;
		}
		
		public function process()
		{
			return $this;
		}
		
		public function getName()
		{
			return get_class($this);
		}
		
		public function getParameters()
		{
			return $this->parameters;
		}
		
		public function setParameters($params = null)
		{
			Assert::isArray($params);

			$this->parameters = $params;
			
			return $this;
		}

		public function addParameters($parameters)
		{
			Assert::isArray($parameters);
			
			$this->parameters = array_merge($this->parameters, $parameters);
			
			return $this;
		}

		public function getTemplatePath($name = null)
		{
			if (!$name)
				$name = $this->getName();
			
			return ModuleFactory::getTemplateDirectory().$name.EXT_TPL;
		}
	}
?>