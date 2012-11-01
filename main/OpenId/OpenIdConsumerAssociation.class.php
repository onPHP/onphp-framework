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

	/**
	 * @ingroup OpenId
	**/
	namespace Onphp;

	interface OpenIdConsumerAssociation
	{
		public function getHandle();
		
		public function getType();
		
		public function getSecret();
		
		/**
		 * @return \Onphp\Timestamp
		**/
		public function getExpires();
		
		/**
		 * @return \Onphp\HttpUrl
		**/
		public function getServer();
	}
?>