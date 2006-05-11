<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	final class RedirectToView extends RedirectView
	{
		private $prefix	= null;
		
		public function __construct($controllerName)
		{
			Assert::isTrue(
				class_exists($controllerName, true)
				&& $controllerName instanceof Controller
			);
			
			$this->url = $controllerName;
		}
		
		public function getPrefix()
		{
			return $this->prefix;
		}
		
		public function setPrefix($prefix)
		{
			$this->prefix = $prefix;
			
			return $this;
		}
		
		public function getName()
		{
			return $this->url;
		}
		
		public function setName($name)
		{
			$this->url = $name;
			
			return $this;
		}
		
		public function render($model = null)
		{
			// saving
			$url = $this->url;
			$this->url = $this->prefix.$this->url;
			
			// processing
			$out = parent::render($model);
			
			// restoring
			$this->url = $url;
			
			// returning!
			return $out;
		}
	}
?>