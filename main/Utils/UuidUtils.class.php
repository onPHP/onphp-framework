<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 28.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Utils
**/
final class UuidUtils extends StaticFactory {

	public static function generate() {
//		if( !extension_loaded('uuid') ) {
//			throw new MissingModuleException('UUID module not found!');
//		}
//
//		return uuid_create(UUID_TYPE_DEFAULT);
//
		return
			sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
					mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
					mt_rand( 0, 0x0fff ) | 0x4000,
					mt_rand( 0, 0x3fff ) | 0x8000,
					mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );

	}

	public static function check( $uuid ) {
		return Assert::checkUniversalUniqueIdentifier( $uuid );
	}

	public static function assert( $uuid ) {
		Assert::isUniversalUniqueIdentifier( $uuid );
	}



}
