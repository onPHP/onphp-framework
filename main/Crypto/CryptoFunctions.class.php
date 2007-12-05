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
	 * @ingroup Crypto
	**/
	final class CryptoFunctions extends StaticFactory
	{
		const SHA1_BLOCK_SIZE = 64;
		
		/**
		 * @see http://tools.ietf.org/html/rfc2104
		**/
		public static function hmacsha1($key, $message)
		{
			if (strlen($key) > self::SHA1_BLOCK_SIZE)
				$key = sha1($key, true);
			
			$key = str_pad($key, self::SHA1_BLOCK_SIZE, "\x00", STR_PAD_RIGHT);
			
			$ipad = null;
			$opad = null;
			for ($i = 0; $i < self::SHA1_BLOCK_SIZE; $i++) {
				$ipad .= "\x36" ^ $key[$i];
				$opad .= "\x5c" ^ $key[$i];
			}
			
			return sha1($opad.sha1($ipad.$message, true), true);
		}
	}
?>