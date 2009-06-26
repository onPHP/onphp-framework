<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	class PartViewer
	{
		protected $viewResolver 	= null;
		protected $model			= null;
		
		public function __construct(ViewResolver $resolver, $model = null)
		{
			$this->viewResolver = $resolver;
			$this->model = $model;
		}
		
		/**
		 * @return PartViewer
		**/
		public function view($partName, $model = null)
		{
			Assert::isTrue($model === null || $model instanceof Model);
			
			// use model from outer template if none specified
			if ($model === null) {
				$model = $this->model;
				
				$parentModel = $this->model->has('parentModel')
					? $this->model->get('parentModel')
					: null;
				
			} else
				$parentModel = $this->model;
			
			$model->set('parentModel', $parentModel);
			
			$rootModel = $this->model->has('rootModel')
				? $this->model->get('rootModel')
				: $this->model;
			
			$model->set('rootModel', $rootModel);
			
			if ($partName instanceof View)
				$partName->render($model);
			else
				$this->viewResolver->resolveViewName($partName)->render($model);
			
			return $this;
		}
		
		public function partExists($partName)
		{
			return $this->viewResolver->viewExists($partName);
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