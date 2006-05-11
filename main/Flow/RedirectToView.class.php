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
	final class RedirectToView implements Stringable
	{
		const PREFIX = 'redirect:';
		
		private $name = null;
		
		public function __construct($controllerName)
		{
			Assert::isTrue(
				class_exists($controllerName, true)
				&& $controllerName instanceof Controller
			);
			
			$this->name = $controllerName;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public function toString()
		{
			return self::PREFIX.$this->name;
		}
	}
?>