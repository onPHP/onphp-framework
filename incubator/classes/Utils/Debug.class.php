<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Alexander S Evdokimov                      *
 *   alexan@x-pro.ru                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Класс, используемый для отладки.
	 *
	 * @todo Мыльник выхлопов Exception'ов (Например, ктр в www/index.html)
	 */
	final class Debug
	{		
		private function __construct() {/* */}
		
		//  Функционал var_dump(), но по человечески
		public static function dump($var)
		{
			echo "<pre>".var_export($var,true)."</pre>";
		}
		
		// Отобразить сформированный запрос
		public static function dumpQuery($query)
		{
			if ($query instanceof SelectQuery)
				echo $query->toString(DBFactory::getDefaultInstance()->getDialect());
		}
	}
?>