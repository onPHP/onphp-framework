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

	// TODO: make it abstract and non-immutable?
	class NavigationArea
	{
		protected $name		= null;
		protected $action	= null;
		protected $model	= null;
		
		public function __construct($name, $action = null, $model = null)
		{
			if ($model)
				Assert::isTrue($model instanceof Model);
			
			$this->name = $name;
			$this->action = $action;
			$this->model = $model;
		}
		
		/**
		 * @return NavigationArea
		**/
		public static function create($name, $action = null, $model = null)
		{
			Assert::isTrue(!$model || ($model instanceof Model));
			
			return new self($name, $action, $model);
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getAction()
		{
			return $this->action;
		}
		
		/**
		 * @return Model
		**/
		public function getModel()
		{
			return $this->model;
		}
	}
?>