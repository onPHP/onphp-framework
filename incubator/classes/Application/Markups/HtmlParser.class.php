<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class HtmlParser
	{
		private $content	= null;
		
		private $buffer		= null;
		private $char		= null;
		private $pos		= null;
		
		private $insideTag	= false;
		
		private $tokens		= array();
		
		public function __construct($content)
		{
			$this->content = $content;
		}
		
		public function parse()
		{
			for (
				$this->pos = 0;
				$this->pos <= mb_strlen($this->content);
				$this->pos++
			) {
				if ($this->pos == mb_strlen($this->content)) {
					$this->char = null; // eof
				} else {
					// TODO: find faster way
					$this->char = mb_substr($this->content, $this->pos, 1);
				}
					
				if (!$this->insideTag)
					$this->notInsideTagState();
				else
					$this->insideTagState();
			}
		}
		
		public function getTokens()
		{
			return $this->tokens;
		}
		
		private function notInsideTagState()
		{
			switch ($this->char) {
				case null: // eof
					$this->dumpBufferAction();
					break;
					
				case '<':
					$this->dumpBufferAction();
					$this->buffer .= $this->char;
					
					$this->insideTag = true;
					break;
					
				default:
					$this->buffer .= $this->char;
			}
		}
		
		private function insideTagState()
		{
			switch ($this->char) {
				case null: // eof
					$this->dumpBufferAction();
					break;
				
				case '>':
					$this->buffer .= $this->char;
					$this->dumpBufferAction();
					
					$this->insideTag = false;
					break;
					
				default:
					$this->buffer .= $this->char;
			}
		}
		
		private function dumpBufferAction()
		{
			if ($this->buffer)
				$this->tokens[] = $this->buffer;
			
			$this->buffer = null;
		}
	}
?>