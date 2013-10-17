<?php
/***************************************************************************
 *   Copyright (C) 2013 by Nikita V. Konstantinov                          *
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
	abstract class HttpController implements Controller
	{
		/**
		 * @return ModelAndView
		**/
		protected function redirect($url, $status = HttpStatus::CODE_302)
		{
			return
				RedirectResponse::create()->
					setUrl($url)->
					setStatus(new HttpStatus($status))
			;
		}

		/**
		 * @return ModelAndView
		**/
		protected function createNotFoundResponse()
		{
			return
				ModelAndView::create()->
					setStatus(
						new HttpStatus(HttpStatus::CODE_404)
					)
			;
		}

		/**
		 * @return ModelAndView
		**/
		protected function createForbiddenResponse()
		{
			return
				ModelAndView::create()->
					setStatus(
						new HttpStatus(HttpStatus::CODE_403)
					)
			;
		}
	}
?>
