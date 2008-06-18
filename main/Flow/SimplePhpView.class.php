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
	class SimplePhpView extends EmptyView
	{
		protected $templatePath		= null;
		protected $partViewResolver	= null;
		
		public function __construct($templatePath, ViewResolver $partViewResolver)
		{
			$this->templatePath = $templatePath;
			$this->partViewResolver = $partViewResolver;
		}
		
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
		
		public function toString($model = null)
		{
			ob_start();
			$this->render($model);
			return ob_get_clean();
		}
		
		protected function preRender()
		{
			return $this;
		}
		
		protected function postRender()
		{
			return $this;
		}
	}
?>