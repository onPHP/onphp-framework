<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	**/
	class ModelAndView
	{
		private $model 	= null;
		
		private $view	= null;
		
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			$this->model = new Model();
		}
		
		public function getModel()
		{
			return $this->model;
		}
		
		public function setModel(Model $model)
		{
			$this->model = $model;
			
			return $this;
		}
		
		public function getView()
		{
			return $this->view;
		}
		
		public function setView($view)
		{
			Assert::isTrue(
				($view instanceof View)	|| is_string($view),
				'do not know, what to do with such view'
			);
			
			$this->view = $view;
			
			return $this;
		}
	}
?>