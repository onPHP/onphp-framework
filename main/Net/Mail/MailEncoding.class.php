<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Mail
	**/
	final class MailEncoding extends Enumeration
	{
		const SEVEN_BITS		= 0x01;
		const EIGHT_BITS		= 0x02;
		const BASE64			= 0x03;
		const QUOTED			= 0x04;
		
		protected $names = array(
			self::SEVEN_BITS	=> '7bit',
			self::EIGHT_BITS	=> '8bit',
			self::BASE64		=> 'base64',
			self::QUOTED		=> 'quoted-printable'
		);
		
		/**
		 * @return MailEncoding
		**/
		public static function seven()
		{
			return new self(self::SEVEN_BITS);
		}
		
		/**
		 * @return MailEncoding
		**/
		public static function eight()
		{
			return new self(self::EIGHT_BITS);
		}
		
		/**
		 * @return MailEncoding
		**/
		public static function base64()
		{
			return new self(self::BASE64);
		}
		
		/**
		 * @return MailEncoding
		**/
		public static function quoted()
		{
			return new self(self::QUOTED);
		}
	}
?>