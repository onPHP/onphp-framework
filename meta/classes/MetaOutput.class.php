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

	final class MetaOutput
	{
		private $out = null;
		
		public function __construct(TextOutput $out)
		{
			$this->out = $out;
		}
		
		/**
		 * @return \Onphp\TextOutput
		**/
		public function getOutput()
		{
			return $this->out;
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function newLine()
		{
			$this->out->newLine();
			
			return $this;
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function log($text, $bold = false)
		{
			return $this->defaultText($text, ConsoleMode::FG_WHITE, $bold);
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function logLine($text, $bold = false)
		{
			return $this->defaultTextLine($text, ConsoleMode::FG_WHITE, $bold);
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function info($text, $bold = false)
		{
			return $this->defaultText($text, ConsoleMode::FG_GREEN, $bold);
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function infoLine($text, $bold = false)
		{
			return $this->defaultTextLine($text, ConsoleMode::FG_GREEN, $bold);
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function warning($text)
		{
			return $this->defaultText($text, ConsoleMode::FG_BROWN, true);
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function warningLine($text)
		{
			return $this->defaultTextLine($text, ConsoleMode::FG_BROWN, true);
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function error($text, $bold = false)
		{
			return $this->errorText($text, ConsoleMode::FG_RED, $bold);
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function errorLine($text, $bold = false)
		{
			return $this->errorTextLine($text, ConsoleMode::FG_RED, $bold);
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function remark($text)
		{
			return $this->defaultText($text, ConsoleMode::FG_BLUE, true);
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		public function remarkLine($text)
		{
			return $this->defaultTextLine($text, ConsoleMode::FG_BLUE, true);
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		private function defaultText($text, $color, $bold)
		{
			$this->out->
				setMode(
					$bold ? ConsoleMode::ATTR_BOLD : ConsoleMode::ATTR_RESET_ALL,
					$color,
					ConsoleMode::BG_BLACK
				)->
				write($text);
			
			if ($this->out instanceof ColoredTextOutput)
				$this->out->resetAll();
			
			return $this;
		}
		
		/**
		 * @return \Onphp\MetaOutput
		**/
		private function defaultTextLine($text, $color, $bold)
		{
			$this->out->
				setMode(
					$bold ? ConsoleMode::ATTR_BOLD : ConsoleMode::ATTR_RESET_ALL,
					$color,
					ConsoleMode::BG_BLACK
				)->
				writeLine($text);
			
			if ($this->out instanceof ColoredTextOutput)
				$this->out->resetAll();
			
			return $this;
		}

		/**
		 * @return MetaOutput
		**/
		private function errorText($text, $color, $bold)
		{
			if ($this->out instanceof ColoredTextOutput)
				$text = $this->out->wrapString($text);

			$this->out->writeErr($text);

			return $this;
		}

		/**
		 * @return MetaOutput
		**/
		private function errorTextLine($text, $color, $bold)
		{
			if ($this->out instanceof ColoredTextOutput)
				$text = $this->out->wrapString($text);

			$this->out->writeErrLine($text);

			return $this;
		}
	}
