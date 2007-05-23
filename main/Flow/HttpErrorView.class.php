<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
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
	class HttpErrorView implements View
	{
		protected $prefix	= null;
		protected $code		= null;
		protected $postfix	= null;
		
		public function __construct($prefix, $code, $postfix)
		{
			$this->prefix = $prefix;
			$this->code = $code;
			$this->postfix = $postfix;
		}
		
		public function render($model = null)
		{
			$status = new HttpStatus($this->code);
			header($status->toString());
			include $this->prefix.$this->code.$this->postfix;
		}
	}
?>