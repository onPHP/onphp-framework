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
	 * @ingroup OpenId
	**/
	final class OpenIdConsumerPositive implements OpenIdConsumerResult
	{
		private $identity = null;
		
		public function __construct(HttpUrl $identity)
		{
			$this->identity = $identity;
		}
		
		/**
		 * @return HttpUrl
		**/
		public function getIdentity()
		{
			return $this->identity;
		}
		
		public function isOk()
		{
			return true;
		}
	}
?>