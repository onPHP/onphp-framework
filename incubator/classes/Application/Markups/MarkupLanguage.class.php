<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	final class MarkupLanguage extends StaticFactory
	{
		/**
		 * @return WmlLanguage
		**/
		public static function wml($version = null)
		{
			$wml = new WmlLanguage();
			
			if ($version)
				$wml->setVersion($version);
			
			return $wml;
		}
		
		/**
		 * @return HtmlLanguage
		**/
		public static function html()
		{
			return new HtmlLanguage();
		}
		
		/**
		 * @return XhtmlMpLanguage
		**/
		public static function xhtmlMp()
		{
			return new XhtmlMpLanguage();
		}
		
		/**
		 * @return BaseMarkupLanguage
		**/
		public static function byCommonName($name, $version = null)
		{
			switch ($name) {
				
				case 'html':
					return self::html($version);
				
				case 'wml':
					return self::wml($version);
				
				case 'xhtmlmp':
					return self::xhtmlMp($version);
				
				default:
					throw new WrongArgumentException(
						'unsupported markup language'
					);
			}
			
			Assert::isUnreachable();
		}
	}
?>