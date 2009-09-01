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

	class SimpleNavigationSchema implements NavigationSchema
	{
		const FRONT_CONTROLLER	= 'index.php';
		
		const AREA_HOLDER		= 'area';
		const ACTION_HOLDER		= 'action';
		
		/**
		 * @return SimpleNavigationSchema
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getNavigationUrl(NavigationArea $area)
		{
			$query = array();
			
			if ($area->getName())
				$query[self::AREA_HOLDER] = $area->getName();
			
			if ($area->getAction())
				$query[self::ACTION_HOLDER] = $area->getAction();
			
			if ($area->getModel())
				foreach ($area->getModel()->getList() as $key => $value) {
					if ($key === self::AREA_HOLDER)
						continue;
					
					if ($key === self::ACTION_HOLDER)
						continue;
					
					$query[$key] = $value;
				}
			
			$result = self::FRONT_CONTROLLER;
			
			if ($query)
				$result .= '?'.http_build_query($query);
			
			return $result;
		}
		
		/**
		 * @return NavigationArea
		**/
		public function getArea($navigationUrl)
		{
			$parts = explode('?', $navigationUrl, 2);
			
			if (isset($parts[0]) && $parts[0])
				$frontController = $parts[0];
			else
				$frontController = self::FRONT_CONTROLLER;
			
			if ($frontController !== self::FRONT_CONTROLLER)
				throw new WrongArgumentException(
					'location settings or rewrites is broken?'
				);
			
			$query = array();
			
			if (isset($parts[1]))
				parse_str($parts[1], $query);
			
			$area = $action = $model = null;
			
			if (isset($query[self::AREA_HOLDER])) {
				$area = $query[self::AREA_HOLDER];
				unset($query[self::AREA_HOLDER]);
			}
			
			if (isset($query[self::ACTION_HOLDER])) {
				$action = $query[self::ACTION_HOLDER];
				unset($query[self::ACTION_HOLDER]);
			}
			
			$model = Model::create();
			
			if ($query) {
				foreach ($query as $key => $value)
					$model->set($key, $value);
			}
			
			return new NavigationArea($area, $action, $model);
		}
	}
?>