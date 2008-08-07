<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	class HttpErrorView implements View
	{
		protected $status	= null;
		
		protected $prefix	= null;
		protected $postfix	= null;
		
		public function __construct(HttpStatus $status, $prefix, $postfix)
		{
			$this->status = $status;
			
			$this->prefix = $prefix;
			$this->postfix = $postfix;
		}
		
		/* void */ public function render($model = null)
		{
			header($this->status->toString());
			include $this->prefix.$this->status->getId().$this->postfix;
		}
	}
?>