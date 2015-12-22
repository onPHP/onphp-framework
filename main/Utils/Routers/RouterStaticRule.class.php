<?php
/***************************************************************************
 *   Copyright (C) 2008 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class RouterStaticRule extends RouterBaseRule
	{
		protected $route	= null;
		
		/**
		 * @return RouterStaticRule
		**/
		public static function create($route)
		{
			return new self($route);
		}
		
		public function __construct($route)
		{
			// FIXME: rtrim. probably?
			$this->route = trim($route, '/');
		}
		
		public function match(HttpRequest $request)
		{
			$path = $this->processPath($request)->toString();
			
			// FIXME: rtrim, probably?
			if (trim(urldecode($path), '/') == $this->route)
				return $this->defaults;
			
			return false;
		}
		
		public function assembly(
			array $data = array(),
			$reset = false,
			$encode = false
		)
		{
			return $this->route;
		}
	}
?>