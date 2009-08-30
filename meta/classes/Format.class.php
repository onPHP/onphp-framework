<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup MetaBase
	**/
	final class Format extends StaticFactory
	{
		// dumb and straigh beautifier
		public static function indentize($data)
		{
			$out	= null;
			
			$indent	= 0;
			$chain	= 1;
			
			$return	= false;
			
			foreach (explode("\n", $data) as $string) {
				$string = str_replace("\t", null, rtrim($string))."\n";
				
				if ($string == "}\n") {
					$indent -= $chain;
					$chain = 1;
				} elseif ($string == ")->\n")
					$indent--;
				elseif ($string == ")\n")
					$indent--;
				elseif ($string == ");\n")
					$indent--;
				elseif ($string == "?>\n")
					$indent = 0;
				elseif ($string[0] == '?')
					$indent++;
				
				if ($indent > 0)
					$out .= str_pad(null, $indent, "\t", STR_PAD_LEFT).$string;
				else
					$out .= $string;

				if (substr($string, -2 ,2) == "{\n")
					$indent++;
				elseif (
					$string[0] == '$'
					&& (
						substr($string, -2, 2) == "=\n"
						|| substr($string, -3, 3) == "->\n"
					)
				) {
					$indent++;
					$chain++;
				} elseif (substr($string, -2, 2) == "(\n")
					$indent++;
				elseif ($string == "\n" && $indent == 0) {
					$indent++;
				} elseif ($string == "return\n") {
					$indent++;
					$chain++;
				} elseif ($string == "\n" && $chain > 1) {
					$indent -= $chain - 1;
					$chain = 1;
				} elseif ($string[0] == ':') {
					$indent--;
				}
			}
			
			return $out;
		}
	}
?>