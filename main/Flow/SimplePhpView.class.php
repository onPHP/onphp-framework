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
	class SimplePhpView implements View 
	{
		private $templatePath		= null;
		private $partViewResolver	= null;
		private $notCached			= null;
		
		public function __construct($templatePath, ViewResolver $partViewResolver)
		{
			$this->templatePath = $templatePath;
			$this->partViewResolver = $partViewResolver;
		}
		
		public function setNotCached()
		{
			$this->notCached = true;
			return $this;
		}
		
		public function render($model = null)
		{
			Assert::isTrue($model === null || $model instanceof Model);
			
			if ($model)
				extract($model->getList());
			
			$partViewer = new PartViewer($this->partViewResolver, $model);
			
			if ($this->notCached)
				HeaderUtils::sendNotCachedHeader();
				
			require $this->templatePath;
		}
	}
?>