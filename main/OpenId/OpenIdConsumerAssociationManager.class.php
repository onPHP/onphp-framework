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

	interface OpenIdConsumerAssociationManager
	{
		/**
		 * @return \Onphp\OpenIdConsumerAssociation
		**/
		public function findByHandle($handle, $type);
		
		/**
		 * @return \Onphp\OpenIdConsumerAssociation
		**/
		public function findByServer(HttpUrl $server);
		
		/**
		 * @return \Onphp\OpenIdConsumerAssociation
		**/
		public function makeAndSave(
			$handle,
			$type,
			$secred,
			Timestamp $expires,
			HttpUrl $server
		);
		
		/**
		 * @return \Onphp\OpenIdConsumerAssociationManager
		**/
		public function purgeExpired();
		
		/**
		 * @return \Onphp\OpenIdConsumerAssociationManager
		**/
		public function purgeByHandle($handle);
	}
?>