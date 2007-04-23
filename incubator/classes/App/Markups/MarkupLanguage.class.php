<?
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
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