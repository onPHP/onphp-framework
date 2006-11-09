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
	 * @ingroup MetaBase
	**/
	final class MetaOutput
	{
		private $out = null;
		
		public function __construct(TextOutput $out)
		{
			$this->out = $out;
		}
		
		public function getOutput()
		{
			return $this->out;
		}
		
		public function newLine()
		{
			$this->out->newLine();
			
			return $this;
		}
		
		public function log($text, $bold = false)
		{
			return $this->defaultText($text, ConsoleMode::FG_WHITE, $bold);
		}
		
		public function logLine($text, $bold = false)
		{
			return $this->defaultTextLine($text, ConsoleMode::FG_WHITE, $bold);
		}
		
		public function info($text, $bold = false)
		{
			return $this->defaultText($text, ConsoleMode::FG_GREEN, $bold);
		}
		
		public function infoLine($text, $bold = false)
		{
			return $this->defaultTextLine($text, ConsoleMode::FG_GREEN, $bold);
		}
		
		public function warning($text)
		{
			return $this->defaultText($text, ConsoleMode::FG_BROWN, true);
		}
		
		public function warningLine($text)
		{
			return $this->defaultTextLine($text, ConsoleMode::FG_BROWN, true);
		}
		
		public function error($text, $bold = false)
		{
			return $this->defaultText($text, ConsoleMode::FG_RED, $bold);
		}
		
		public function errorLine($text, $bold = false)
		{
			return $this->defaultTextLine($text, ConsoleMode::FG_RED, $bold);
		}
		
		public function remark($text)
		{
			return $this->defaultText($text, ConsoleMode::FG_BLUE, true);
		}
		
		public function remarkLine($text)
		{
			return $this->defaultTextLine($text, ConsoleMode::FG_BLUE, true);
		}
		
		private function defaultText($text, $color, $bold)
		{
			$this->out->
				setMode(
					$bold ? ConsoleMode::ATTR_BOLD : ConsoleMode::ATTR_RESET_ALL,
					$color,
					ConsoleMode::BG_BLACK
				)->
				write($text);
			
			return $this;
		}
		
		private function defaultTextLine($text, $color, $bold)
		{
			$this->out->
				setMode(
					$bold ? ConsoleMode::ATTR_BOLD : ConsoleMode::ATTR_RESET_ALL,
					$color,
					ConsoleMode::BG_BLACK
				)->
				writeLine($text);
			
			return $this;
		}
	}
?>