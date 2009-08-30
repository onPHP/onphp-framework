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
	class SimplePhpView implements View, Stringable
	{
		private $templatePath		= null;
		private $partViewResolver	= null;
		
		public function __construct($templatePath, ViewResolver $partViewResolver)
		{
			$this->templatePath = $templatePath;
			$this->partViewResolver = $partViewResolver;
		}
		
		public function render($model = null)
		{
			Assert::isTrue($model === null || $model instanceof Model);
			
			if ($model)
				extract($model->getList());
			
			$partViewer = new PartViewer($this->partViewResolver, $model);
			
			include $this->templatePath;
		}
		
		public function toString($model = null)
		{
			ob_start();
			$this->render($model);
			return ob_get_clean();
		}
	}
?>