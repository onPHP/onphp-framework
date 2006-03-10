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
			if (!$mav = $this->handleRequestInternal($request))
				$mav =
					ModelAndView::create()->
					setModel(
						$this->dumpProtected($this->makeModel())
					);
			
			return $mav;
		}
		
		protected function dumpProtected(Model $model)
		{
			$class = new ReflectionClass($this);
			
			foreach ($class->getProperties() as $property) {
				if ($property->isProtected() && !$property->isStatic())
					$model->setVar(
						$property->getName(),
						$this->{$property->getName()}
					);
			}
			
			return $model;
		}
		
		protected function makeModel()
		{
			return new Model();
		}
		
		protected function blowOut($area = DEFAULT_MODULE)
		{
			return
				ModelAndView::create()->setView(
					new RedirectView()
				)->
				setModel(
					Model::create()->setVar('area', $area)
				);
		}
	}
?>