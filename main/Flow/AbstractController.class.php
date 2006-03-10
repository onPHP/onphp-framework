<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
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
	abstract class AbstractController implements Controller
	{
		/**
		 * @return ModelAndView
		**/
		abstract protected function handleRequestInternal(HttpRequest $request);
		
		public function handleRequest(HttpRequest $request)
		{
			return
				$this->
					handleRequestInternal($request)->
						setModel($this->dumpProtected(new Model()));
		}
		
		protected function dumpProtected(Model $model)
		{
			$class = new ReflectionClass($this);
			
			foreach ($class->getProperties() as $property) {
				if ($property->isProtected())
					$model->setVar(
						$property->getName(),
						$this->{$property->getName()}
					);
			}
			
			return $model;
		}
	}
?>