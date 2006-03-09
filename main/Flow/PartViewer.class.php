<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
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
	class PartViewer
	{
		private $viewResolver = null;
		
		public function __construct(ViewResolver $resolver)
		{
			$this->viewResolver = $resolver;
		}
		
		public function view($partName, $model = null)
		{
			Assert::isTrue($model === null || $model instanceof Model);
			
			$this->viewResolver->resolveViewName($partName)->render($model);
			
			return $this;
		}
	}
?>