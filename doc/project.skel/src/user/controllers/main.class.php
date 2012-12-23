<?php
/***************************************************************************
 *   Copyright (C) 2006 by Unknown Hero                                    *
 *   non.existent.login@forgotten.host                                     *
 ***************************************************************************/
/* $Id$ */

	class main implements Controller
	{
		public function handleRequest(HttpRequest $request)
		{
			if (1 === 1)
				return ModelAndView::create()->setView('main');
			else
				throw new WrongStateException('everything is b0rked');
		}
	}
