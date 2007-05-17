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
		const INITIAL_STATE		= 0;
		const START_TAG_STATE	= 1;
		const END_TAG_STATE		= 2;
		const END_TAG_ID_STATE	= 3;
		const INSIDE_TAG_STATE	= 4;
		const FINAL_STATE		= 42;
		
		const SPACER_MASK			= '[ \r\n\t]';
		const ID_FIRST_CHAR_MASK	= '[A-Za-z]';
		const ID_CHAR_MASK			= '[-_:.A-Za-z0-9]';
		
		private $content	= null;
		private $length		= null;
		
		private $char		= null;
		private $pos		= 0;
		
		// for logging
		private $line			= 1;
		private $linePosition	= 1;
		private $previousChar	= null;
		
		private $state		= self::INITIAL_STATE;
		
		private $tags		= array();
		private $errors		= array();
		
		private $buffer		= null;
		
		private $tagId			= null;
		private $invalidTagId	= false;
		private $tag			= null;
		
		public function __construct($content)
		{
			$this->content = $content;
			$this->length = mb_strlen($this->content);
		}
		
		/**
		 * @return HtmlParser
		**/
		public function parse()
		{
			$this->getNextChar();
			
			while ($this->state != self::FINAL_STATE)
				$this->state = $this->handleState();
			
			if ($this->char !== null)
				$this->error("extra characters");
			
			return $this;
		}
		
		public function getTags()
		{
			return $this->tags;
		}
		
		public function getErrors()
		{
			return $this->errors;
		}
		
		private function getNextChar()
		{
			if ($this->pos == $this->length)
				$this->char = null; // eof
			else {
				$this->char = mb_substr($this->content, $this->pos, 1);
				++$this->pos;
			}
			
			if (
				$this->char == "\n" && $this->previousChar != "\r"
				|| $this->char == "\r"
			) {
				++$this->line;
				$this->linePosition = 1;
			}
				
			$this->previousChar = $this->char;
			
			return $this->char;
		}
		
		private function handleState()
		{
			switch ($this->state) {
				case self::INITIAL_STATE:
					return $this->outsideTagState();
					
				case self::START_TAG_STATE:
					return $this->startTagState();
					
				case self::END_TAG_STATE:
					return $this->endTagState();
					
				case self::END_TAG_ID_STATE:
					return $this->endTagIdState();
					
				case self::INSIDE_TAG_STATE:
					return $this->insideTagState();
					
				default:
					throw WrongStateException('state machine is broken');
			}
			
			Assert::isUnreachable();
		}
		
		// INITIAL_STATE
		private function outsideTagState()
		{
			if ($this->char === null) {
				// [end-of-file]
				
				if ($this->buffer) {
					$this->tags[] = Cdata::create()->setData($this->buffer);
					$this->buffer = null;
				}
				
				return self::FINAL_STATE;
					
			} elseif ($this->char == '<') {
			
				$this->getNextChar();
				
				if (preg_match('/'.self::ID_FIRST_CHAR_MASK.'/', $this->char)) {
					
					if ($this->buffer) {
						$this->tags[] = Cdata::create()->setData($this->buffer);
						$this->buffer = null;
					}
					
					$this->tagId = $this->char;
					$this->invalidTagId = false;
					
					$this->getNextChar();
					
					return self::START_TAG_STATE;
					
				} elseif ($this->char == '/') {
					// </
					
					if ($this->buffer) {
						$this->tags[] = Cdata::create()->setData($this->buffer);
						$this->buffer = null;
					}
					
					$this->getNextChar();
					
					return self::END_TAG_STATE;
					
				} else {
					// <2, <ф, <[space], <>
					
					$this->warning(
						"incorrect start-tag, treating it as cdata"
					);
					
					$this->buffer .= '<'.$this->char;
					
					$this->getNextChar();
					
					return self::INITIAL_STATE;
				}
				
				Assert::isUnreachable();
					
			} else {
				
				$this->buffer .= $this->char;
				$this->getNextChar();
					
				return self::INITIAL_STATE;
			}
			
			Assert::isUnreachable();
		}
		
		// START_TAG_STATE
		private function startTagState()
		{
			if ($this->char === null) {
				// ... <tag[end-of-file]
				
				$this->error("unexpected end of file");
				
				return self::FINAL_STATE;
				
			} elseif ($this->char == '>') {
				// <b>, <bмусор>
				
				$this->tags[] = SgmlOpenTag::create()->
					setId($this->tagId);
				
				$this->tagId = null;
				$this->invalidTagId = false;
				
				$this->getNextChar();
				
				return self::INITIAL_STATE;
				
			} elseif (preg_match('/'.self::SPACER_MASK.'/', $this->char)) {
				// <p[space], <divмусор[space]
				
				$this->tag = SgmlOpenTag::create()->
					setId($this->tagId);
				
				$this->tags[] = $this->tag;
				
				$this->getNextChar();
				
				return self::INSIDE_TAG_STATE;
				
			} else {
				// <div, <q#, <dж
				
				if (!preg_match('/'.self::ID_CHAR_MASK.'/', $this->char)) {
					// most browsers seems like parsing invalid tags
					
					$this->error(
						"tag id contains invalid char with code "
						.self::charHexCode($this->char)
						.", parsing with invalid id"
					);
					
					$this->invalidTagId = true;
				}
				
				$this->tagId .= $this->char;
					
				$this->getNextChar();
				
				return self::START_TAG_STATE;
			}
			
			Assert::isUnreachable();
		}
		
		// END_TAG_STATE
		private function endTagState()
		{
			if ($this->char === null) {
				// ... </[end-of-file]
				
				$this->error("unexpected end of file");
				
				return self::FINAL_STATE;
				
			} elseif ($this->char == '>') {
				
				if (!$this->tagId) {
					// </>
					$this->warning('ignoring empty/invalid end-tag');
					
				} else {
					// storing
					
					$this->tags[] =
						SgmlEndTag::create()->
						setId($this->tagId);
					
					$this->tagId = null;
				}
				
				$this->invalidTagId = false;
				
				$this->getNextChar();
				
				return self::INITIAL_STATE;
					
			} else {
				// most browsers parsing end-tag until next '>' char
				
				$validChar =
					(
						!$this->tagId
						&& preg_match('/'.self::ID_FIRST_CHAR_MASK.'/', $this->char)
					)
					||
					(
						$this->tagId
						&& preg_match('/'.self::ID_CHAR_MASK.'/', $this->char)
					);
				
				if (!$validChar)
					$this->error(
						"id contains invalid char with code "
						.self::charHexCode($this->char)
						.", storing end-tag with invalid id"
					);
					
				$this->tagId .= $this->char;
				
				$this->getNextChar();
				
				return self::END_TAG_STATE;
				
			}
			
			Assert::isUnreachable();
		}
		
		// INSIDE_TAG_STATE
		private function insideTagState()
		{
			throw new UnimplementedFeatureException('inside tag');
		}
		
		private function getTextualPosition()
		{
			return
				"line {$this->line}, position {$this->linePosition}"
				.(
					$this->tag && $this->tag->getId()
					? ", in tag '{$this->tag->getId()}'"
					: null
				);
		}
		
		/**
		 * @return HtmlParser
		**/
		private function warning($message)
		{
			$this->errors[] =
				"warning at {$this->getTextualPosition()}: $message";
			
			return $this;
		}
		
		/**
		 * @return HtmlParser
		**/
		private function error($message)
		{
			$this->errors[] =
				"error at {$this->getTextualPosition()}: $message";
			
			return $this;
		}
		
		private static function charHexCode($char)
		{
			// FIXME: sprintf!
			return '0x'.dechex(ord($char));
		}
	}
?>