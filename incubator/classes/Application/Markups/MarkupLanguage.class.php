<?
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class MarkupLanguage extends StaticFactory
	{
		public static function wml($version = null)
		{
			$result = new WmlLanguage;

			if ($version)
				$result->setVersion($version);
		}

		public static function xhtmlMp()
		{
			return new XhtmlMpLanguage;
		}

		public static function byCommonName($name, $version = null)
		{
			switch ($name) {

				case 'wml':
					return self::wml($version);

				case 'xhtmlmp':
					return self::xhtmlMp($version);

				default:
					throw
						new WrongArgumentException(
							'unsupported markup language'
						);
			}
		}
	}
?>