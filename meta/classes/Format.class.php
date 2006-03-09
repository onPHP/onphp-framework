<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class Format extends StaticFactory
	{
		// dumb and straigh beautifier
		public static function indentize($data)
		{
			$out	= null;
			
			$indent	= 0;
			$chain	= 0;
			
			$return	= false;
			
			foreach (explode("\n", $data) as $string) {
				$string = str_replace("\t", null, $string)."\n";
				
				$indent -= substr_count($string, '}');
				
				if ($string == ")->\n")
					$indent--;
				elseif ($string == ")\n")
					$indent--;
				elseif ($string == "?>\n")
					$indent = 0;
				elseif ($string == ");\n") {
					if ($chain)
						$indent -= $chain;
					else
						$indent--;
				}

				if ($indent > 0)
					$out .= str_pad(null, $indent, "\t", STR_PAD_LEFT).$string;
				else
					$out .= $string;

				if (
					(strpos($string, "return\n") !== false)
					&& (strpos($string, ';') === false)
				) {
					$indent++;
					$return = true;
				}
				
				if (strpos($string, ");\n") !== false) {
					if ($return) {
						$return = false;
						$indent -= $chain + 1;
						$chain = 0;
					} elseif ($chain) {
						$indent -= $chain;
						$chain--;
					}
				}
				
				$indent += substr_count($string, '{');
				$indent += substr_count($string, "(\n");
				
				if (
					(
						(strpos($string, "->\n") !== false)
						|| (
							strlen($string) > 2
							&& substr($string, -2, 2) == "=\n"
						)
					)
					&& $string[0] == '$'
				) {
					$chain++;
					$indent++;
				}
				
				if ($chain && $string == "\n") {
					$indent -= $chain;
					$chain--;
				}
				
				if ($string == "\n" && $indent == 0) {
					$indent++;
				}
			}
			
			return $out;
		}
	}
?>