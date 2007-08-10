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
				'1/4'	=> '&frac14;',
				'½'		=> '&frac12;',
				'1/2'	=> '&frac12;',
				'¾'		=> '&frac34;',
				'3/4'	=> '&frac34;',
				'±'		=> '&plusmn;',
				'+/-'	=> '&plusmn;',
				'!='	=> '&ne;',
				'<>'	=> '&ne;'
			);
		
		private static $from = array(
			'~\-{2,}~',						// --
			'~([\w\pL]+)\s\-\s~',			// foo - bar
			'~([\s\pP])([\w\pL]{1,2})\s~U',	// bar a foo
			'~\"([^\s]*)\"~',				// "quote"
			'~\"([^\s]*)\s+([^\s\.]*)\"~',	// "quote quote"
			'~\"(.*)\"~e',					// "qu"o"te"
			'~([\w\pL\']+)~e'				// rock'n'roll
		);
		
		private static $to = array(
			'-',
			'$1&nbsp;&#151; ',
			'$1$2&nbsp;',
			'&laquo;$1&raquo;',
			'&laquo;$1 $2&raquo;',
			'\'&laquo;\'.$this->innerQuotes(\'$1\').\'&raquo;\'',
			'str_replace("\'", \'&#146;\', \'$1\')'
		);
		
		private static $chain = null;
		
		protected function __construct()
		{
			self::$chain =
				FilterChain::create()->
				add(
					Filter::trim()
				)->
				add(
					CompressWhitespaceFilter::me()
				);
		}
		
		/**
		 * @return RussianTypograph
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function apply($value)
		{
			if ($value === '')
				return $value;
			
			$list =
				preg_split(
					'~([^<>]*)(?![^<]*?>)~',
					strtr($value, self::$symbols),
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
			
			return self::$chain->apply($text);
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