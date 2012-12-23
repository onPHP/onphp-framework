<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov, Konstantin V. Arkhipov    *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	**/
	final class NullController implements Controller
	{
		private $model = null;
		
		/**
		 * @return NullController
		**/
		public static function create(Model $model = null)
		{
			return new self($model);
		}
		
		public function __construct(Model $model = null)
		{
			$this->model = $model;
		}
		
		/**
		 * @return ModelAndView
		**/
		public function handleRequest(HttpRequest $request)
		{
			$result = ModelAndView::create();
			
			if ($this->model)
				$result->setModel($this->model);
			
			return $result;
		}
	}
