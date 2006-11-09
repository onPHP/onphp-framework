<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
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
	class ModelAndView
	{
		private $model 	= null;
		
		private $view	= null;
		
		/**
		 * @return ModelAndView
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			$this->model = new Model();
		}
		
		/**
		 * @return Model
		**/
		public function getModel()
		{
			return $this->model;
		}
		
		/**
		 * @return ModelAndView
		**/
		public function setModel(Model $model)
		{
			$this->model = $model;
			
			return $this;
		}
		
		public function getView()
		{
			return $this->view;
		}
		
		/**
		 * @return ModelAndView
		**/
		public function setView($view)
		{
			Assert::isTrue(
				($view instanceof View)	|| is_string($view),
				"what should i do with '{$view}'?"
			);
			
			$this->view = $view;
			
			return $this;
		}
	}
?>