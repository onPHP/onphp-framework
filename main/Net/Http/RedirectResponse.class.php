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
	 * @ingroup Http
	**/
	class RedirectResponse extends ModelAndView
	{
		public function __construct()
		{
			parent::__construct();

			$this->setStatus(new HttpStatus(HttpStatus::CODE_302));
		}

		public function setUrl($url)
		{
			$this->getHeaderCollection()->set('Location', $url);

			return $this;
		}

		public function setStatus(HttpStatus $status)
		{
			Assert::isTrue($status->isRedirection());

			return parent::setStatus($status);
		}
	}
?>
