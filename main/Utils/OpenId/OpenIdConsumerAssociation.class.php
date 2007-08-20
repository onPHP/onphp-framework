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

	interface OpenIdConsumerAssociation
	{
		public function getType();
		
		/**
		 * @return OpenIdConsumerAssociation
		**/
		public function setType($type);
		
		public function getHandle();
		
		/**
		 * @return OpenIdConsumerAssociation
		**/
		public function setHandle($handle);
		
		public function getSecret();
		
		/**
		 * @return OpenIdConsumerAssociation
		**/
		public function setSecret($secret);
		
		/**
		 * @return OpenIdConsumerAssociation
		**/
		public function setExpires(Timestamp $expires);
		
		/**
		 * @return Timestamp
		**/
		public function getExpires();
		
		/**
		 * @return HttpUrl
		**/
		public function getServer();
		
		/**
		 * @return OpenIdConsumerAssociation
		**/
		public function setServer(HttpUrl $server);
	}
?>