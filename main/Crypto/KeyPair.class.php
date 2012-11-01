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
	 * @ingroup Crypto
	**/
	namespace Onphp;

	interface KeyPair
	{
		/**
		 * @return \Onphp\BigInteger
		**/
		public function getPublic();
		
		/**
		 * @return \Onphp\BigInteger
		**/
		public function getPrivate();
	}
?>