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
	class ModelAndView implements Creatable
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
				"what should i do with '{$view}'?"
			);
			
			$this->view = $view;
			
			return $this;
		}
	}
?>