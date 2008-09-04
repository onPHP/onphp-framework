<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Anton E. Lebedevich                        *
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
	class SimplePhpView extends EmptyView
	{
		protected $templatePath		= null;
		protected $partViewResolver	= null;
		
		public function __construct($templatePath, ViewResolver $partViewResolver)
		{
			$this->templatePath = $templatePath;
			$this->partViewResolver = $partViewResolver;
		}
		
		/**
		 * @return SimplePhpView
		**/
		public function render(Model $model = null)
		{
			if ($model)
				extract($model->getList());
			
			$partViewer = new PartViewer($this->partViewResolver, $model);
			
			$this->preRender();
			
			include $this->templatePath;
			
			$this->postRender();
			
			return $this;
		}
		
		/**
		 * @return ViewResolver
		**/
		public function getResolver()
		{
			return $this->partViewResolver;
		}
		
		/**
		 * @return SimplePhpView
		**/
		public function setResolver(ViewResolver $resolver)
		{
			$this->partViewResolver = $resolver;
			
			return $this;
		}
		
		public function toString($model = null)
		{
			ob_start();
			$this->render($model);
			return ob_get_clean();
		}
		
		/**
		 * @return SimplePhpView
		**/
		protected function preRender()
		{
			return $this;
		}
		
		/**
		 * @return SimplePhpView
		**/
		protected function postRender()
		{
			return $this;
		}
	}
?>