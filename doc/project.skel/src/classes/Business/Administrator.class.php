<?php
/*****************************************************************************
 *   Copyright (C) 2006, onPHP's MetaConfiguration Builder.                  *
 *   Generated by onPHP-0.4.3.99 at 2006-05-07 19:37:11                      *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class Administrator
		extends AutoAdministrator
		implements Prototyped, DAOConnected
	{
		const LABEL = 's1kr33t admin';
		
		public static function create()
		{
			return new self;
		}
		
		public static function dao()
		{
			return Singleton::getInstance('AdministratorDAO');
		}
		
		public static function proto()
		{
			return Singleton::getInstance('ProtoAdministrator');
		}
	}
?>