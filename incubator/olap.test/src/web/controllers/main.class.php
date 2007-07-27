<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	class main implements Controller
	{
		public function handleRequest(HttpRequest $request)
		{
			return ModelAndView::create();
		}
	}
?>