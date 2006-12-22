<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Filters
	 * 
	 * @see http://www.artlebedev.ru/tools/typograf/
	**/
	final class RussianTypograph extends BaseFilter implements Instantiatable
	{
		private static $symbols =
			array(
				'…'		=> '&#133;',
				'...'	=> '&#133;',
				'™'		=> '&trade;',
				'(tm)'	=> '&trade;',
				'(TM)'	=> '&trade;',
				'©'		=> '&copy;',
				'(c)'	=> '&copy;',
				'(C)'	=> '&copy;',
				'№'		=> '&#8470;',
				'—'		=> '&mdash;',
				'–'		=> '&mdash;',
				'«'		=> '&laquo;',
				'»'		=> '&raquo;',
				'•'		=> '&bull;',
				'®'		=> '&reg;',
				'¼'		=> '&frac14;',
				'1/4'	=> '&frac14;',
				'½'		=> '&frac12;',
				'1/2'	=> '&frac12;',
				'¾	'	=> '&frac34;',
				'3/4'	=> '&frac34;',
				'±'		=> '&plusmn;',
				'+/-'	=> '&plusmn;'
			);
		
		private static $from = array(
			'~\-{2,}~',						// --
			'~([\w\pL]+)\s\-\s~',			// foo - bar
			'~\s([\w\pL]{1,2})\s~U',		// a foo
			'~\"(.*)\"~De',					// "qu"o"te"
			'~([\w\pL\']+)~e',					// rock'n'roll
		);
		
		private static $to = array(
			'-',
			'$1&nbsp;&#151; ',
			' $1&nbsp;',
			'\'&laquo;\'.$this->innerQuotes(\'$1\').\'&raquo;\'',
			'str_replace("\'", \'&#146;\', \'$1\')'
		);
		
		/**
		 * @return RussianTypograph
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function apply($value)
		{
			return
				preg_replace(
					self::$from,
					self::$to,
					strtr($value, self::$symbols)
				);
		}
		
		protected function innerQuotes($text)
		{
			return
				preg_replace(
					'~\"(.*)\"~U',
					'&#132;$1&#147;',
					stripslashes($text)
				);
		}
	}
?>