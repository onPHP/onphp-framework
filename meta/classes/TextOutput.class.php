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

	/**
	 * @ingroup MetaBase
	**/
	namespace Onphp;

	class TextOutput
	{
		/**
		 * @return \Onphp\TextOutput
		**/
		public function write($text)
		{
			echo $text;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\TextOutput
		**/
		public function writeLine($text)
		{
			echo $text."\n";
			
			return $this;
		}
		
		/**
		 * @return \Onphp\TextOutput
		**/
		public function newLine()
		{
			echo "\n";
			
			return $this;
		}
		
		/**
		 * @return \Onphp\TextOutput
		**/
		public function setMode(
			$attribute = ConsoleMode::ATTR_RESET_ALL,
			$foreground = ConsoleMode::FG_WHITE,
			$background = ConsoleMode::BG_BLACK
		)
		{
			// nop
			
			return $this;
		}
		
		/**
		 * @return \Onphp\TextOutput
		**/
		public function resetAll()
		{
			// nop
			
			return $this;
		}
	}
?>