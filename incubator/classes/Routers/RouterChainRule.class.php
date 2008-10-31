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
/* $Id$ */

	class RouterChainRule extends RouterBaseRule
	{
		protected $routes		= array();
		protected $separators	= array();
		
		/**
		 * @throws UnimplementedFeatureException
		**/
		public static function create($route /* , ... */)
		{
			throw new UnimplementedFeatureException();
		}
		
		/**
		 * @return RouterChainRule
		**/
		public function chain(RouterRule $route, $separator = '/')
		{
			$this->routes[] = $route;
			$this->separators[] = $separator;
			
			return $this;
		}
		
		public function match(HttpRequest $request)
		{
			$values = array();
			
			foreach ($this->routes as $key => $route) {
				$res = $route->match($request);
				
				if ($res === false)
					return false;
				
				$values = $res + $values;
			}
			
			return $values;
		}
		
		public function assemble(
			$data = array(),
			$reset = false,
			$encode = false
		)
		{
			$value = null;
			
			foreach ($this->routes as $key => $route) {
				if ($key > 0)
					$value .= $this->separators[$key];
				
				$value .= $route->assemble($data, $reset, $encode);
			}
			
			return $value;
		}
	}
?>