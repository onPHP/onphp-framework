<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Filters
	 * 
	 * @see http://www.artlebedev.ru/tools/typograf/
	**/
	final class RussianTypograph extends BaseFilter
	{
		const MAGIC_DELIMITER = '<>'; // brilliant!
		
		private static $symbols =
			array(
				' '		=> ' ', // bovm
				' < '	=> ' &lt; ',
				' > '	=> ' &gt; ',
				'…'		=> '&hellip;',
				'...'	=> '&hellip;',
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
				'„'		=> '&bdquo;',
				'“'		=> '&ldquo;',
				'•'		=> '&bull;',
				'®'		=> '&reg;',
				'¼'		=> '&frac14;',
				'½'		=> '&frac12;',
				'¾'		=> '&frac34;',
				'±'		=> '&plusmn;',
				'+/-'	=> '&plusmn;',
				'!='	=> '&ne;',
				'<>'	=> '&ne;',
				
				// just to avoid regexp's
				' 1/4'	=> ' &frac14;',
				' 1/2'	=> ' &frac12;',
				' 3/4'	=> ' &frac34;',
				'1/4 '	=> '&frac14; ',
				'1/2 '	=> '&frac12; ',
				'3/4 '	=> '&frac34; '
			);
		
		private static $from = array(
			'~\-{2,}~',							// --
			'~([\w\pL\pP]+)\s+\-\s+~u',			// foo - bar
			'~(\s)\s*~u',						// n -> 2 whitespaces to process short strings (bar to a foo)
			'~([\s\pP]|^)([\w\pL]{1,2})\s~Uu',	// bar a foo | bar to a foo
			'~(&nbsp;|\s)\s+~u',				// compress whitespaces
			'~\"([^\s]*)\"~',					// "quote"
			'~\"([^\s]*)\s+([^\s\.]*)\"~',		// "quote quote"
			'~\"(.*)\"~e',						// "qu"o"te"
			'~([\w\pL\']+)~eu'					// rock'n'roll
		);
		
		private static $to = array(
			'-',
			'$1&nbsp;&#151; ',
			'$1$1',
			'$1$2&nbsp;',
			'$1',
			'&laquo;$1&raquo;',
			'&laquo;$1 $2&raquo;',
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
			if (!$value = trim(strtr($value, self::$symbols)))
				return null;
			
			$list =
				preg_split(
					'~([^<>]*)(?![^<]*?>)~',
					$value,
					null,
					PREG_SPLIT_DELIM_CAPTURE
						| PREG_SPLIT_NO_EMPTY
						| PREG_SPLIT_OFFSET_CAPTURE
				);
			
			$tags = array();
			$text = null;
			
			if (count($list) > 1) {
				foreach ($list as $row) {
					$string = $row[0];
					if (
						(strpos($string, '<') === false)
						&& (strpos($string, '>') === false)
					) {
						$text .= $string;
					} else {
						$tags[] = $string;
						$text .= self::MAGIC_DELIMITER;
					}
				}
			} else {
				$text = $list[0][0];
			}
			
			$text = $this->typographize($text);
			
			if ($tags) {
				$i = 0;
				$out = null;
				
				foreach (explode(self::MAGIC_DELIMITER, $text) as $chunk) {
					$out .= $chunk;
					
					if (isset($tags[$i]))
						$out .= $tags[$i++];
				}
				
				return $out;
			}
			
			return CompressWhitespaceFilter::me()->apply($text);
		}
		
		private function typographize($text)
		{
			if (strlen($text) < 2)
				return $text;
			
			return
				preg_replace(
					self::$from,
					self::$to,
					stripslashes($text)
				);
		}
		
		private function innerQuotes($text)
		{
			return
				preg_replace(
					array(
						'~&laquo;(.*)&raquo;~U',
						'~\"(.*)\"~U',
					),
					'&#132;$1&#147;',
					stripslashes($text)
				);
		}
	}
?>